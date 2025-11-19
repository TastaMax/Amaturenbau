<?php

namespace App\ShopWare\Agent;

use App\Http\Controllers\Controller;
use App\Models\SWProduct;
use App\Models\SWProductClass;
use App\Models\SWVariantHeader;
use App\Models\SWVariantValue;
use App\Models\SWVariantCache;
use App\ShopWare\Services\ShopwareApiService;
use App\ShopWare\Services\ShopwareApiHelper;
use Illuminate\Support\Facades\Log;

class ProductSyncController extends Controller
{
    protected ShopwareApiService $shopwareApiService;
    protected ShopwareApiHelper $shopwareApiHelper;
    /**
     * Caches to avoid redundant API calls during a run.
     */
    protected array $ensuredPropertyGroups = [];
    protected array $ensuredPropertyOptions = [];
    protected array $updatedParents = [];
    /**
     * Memory guards
     */
    protected int $chunkSize = 500; // tune as needed
    protected int $maxDetails = 1000; // cap details to avoid huge arrays
    protected int $maxOptionCache = 5000; // bound option cache
    protected int $maxGroupCache = 1000;  // bound group cache
    /**
     * Use Shopware bulk sync API to upsert variants per chunk.
     */
    protected bool $useBulk = true;
    /**
     * Collect parent updates to include in bulk sync (avoid per-parent PATCH).
     */
    protected array $parentsToUpdate = [];
    /**
     * Tracks which configurator option IDs we have already added per parent to avoid duplicates.
     */
    protected array $parentConfiguratorOptionIds = [];

    public function __construct(ShopwareApiService $shopwareApiService, ShopwareApiHelper $shopwareApiHelper)
    {
        $this->shopwareApiService = $shopwareApiService;
        $this->shopwareApiHelper = $shopwareApiHelper;
    }

    public function syncProducts(): array
    {
        try {
            $results = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => [],
                'details' => []
            ];
            // track upserts when using bulk
            $results['upserted'] = 0;

            $hadAny = false;
            $batch = [];
            SWProduct::whereNotNull('articlenumber')
                ->with(['productClass.variantHeaders', 'variantValues'])
                ->orderBy('id')
                ->chunkById($this->chunkSize, function ($chunk) use (&$results, &$hadAny, &$batch) {
                    $hadAny = $hadAny || $chunk->isNotEmpty();
                    foreach ($chunk as $product) {
                        try {
                            if (empty($product->articlenumber)) {
                                $results['skipped']++;
                                $this->pushDetail($results, "Skipped: missing articlenumber for product ID {$product->id}");
                                continue;
                            }

                            $parentId = $this->resolveParentProductId($product);
                            if (!$parentId) {
                                $results['skipped']++;
                                $this->pushDetail($results, "Skipped: could not resolve parent Shopware product for product ID {$product->id}");
                                continue;
                            }

                            $optionIds = $this->ensureVariantOptions($product);

                            $productData = $this->prepareVariantProductData($product, $parentId, $optionIds);

                            if ($this->useBulk) {
                                // collect for bulk upsert
                                $batch[] = $productData;
                            } else {
                                // Faster path: try update by deterministic ID, create on 400(FRAMEWORK__WRITE_TYPE_INTEND_ERROR) or 404
                                $variantId = $productData['id'];
                                try {
                                    $this->shopwareApiService->updateProduct($variantId, $productData);
                                    $results['updated']++;
                                    $this->pushDetail($results, "Updated variant: {$product->articlenumber} (parent: {$parentId})");
                                } catch (\Throwable $t) {
                                    $msg = strtolower($t->getMessage());
                                    $hintCreate = str_contains($msg, 'framework__write_type_intend_error')
                                        || str_contains($msg, 'use post method to create new entities')
                                        || str_contains($msg, '404')
                                        || str_contains($msg, 'not found');
                                    if ($hintCreate) {
                                        // One-time search by productNumber to target legacy random IDs
                                        try {
                                            $found = $this->shopwareApiService->searchProducts([
                                                'filter' => [
                                                    ['field' => 'productNumber', 'type' => 'equals', 'value' => $product->articlenumber]
                                                ],
                                                'limit' => 1
                                            ]);
                                            $existing = $found['data']['data'][0] ?? null;
                                            if ($existing && isset($existing['id'])) {
                                                // align payload id with existing id
                                                $productData['id'] = $existing['id'];
                                                $this->shopwareApiService->updateProduct($existing['id'], $productData);
                                                $results['updated']++;
                                                $this->pushDetail($results, "Updated variant by lookup: {$product->articlenumber} (parent: {$parentId})");
                                            } else {
                                                $response = $this->shopwareApiService->createProduct($productData);
                                                $results['created']++;
                                                $createdId = $response['data']['data']['id'] ?? null;
                                                $this->pushDetail($results, "Created variant: {$product->articlenumber} (parent: {$parentId})" . ($createdId ? " (ID: {$createdId})" : ''));
                                            }
                                        } catch (\Throwable $t2) {
                                            // If search/update fails, final fallback to create
                                            $response = $this->shopwareApiService->createProduct($productData);
                                            $results['created']++;
                                            $createdId = $response['data']['data']['id'] ?? null;
                                            $this->pushDetail($results, "Created variant (fallback): {$product->articlenumber} (parent: {$parentId})" . ($createdId ? " (ID: {$createdId})" : ''));
                                        }
                                    } else {
                                        throw $t;
                                    }
                                }
                            }

                            // Ensure parent configurator settings include all option ids used by its variants (collect for bulk once per parent)
                            $this->ensureParentConfiguratorSettings($product->productClass, $parentId, $optionIds);
                        } catch (\Exception $e) {
                            $errorMsg = "Failed to sync variant '{$product->articlenumber}' (ID: {$product->id}): " . $e->getMessage();
                            Log::error($errorMsg);
                            $results['errors'][] = $errorMsg;
                        }
                    }

                    // Execute bulk upsert at the end of each chunk
                    if ($this->useBulk && (!empty($batch) || !empty($this->parentsToUpdate))) {
                        try {
                            $payload = [];
                            // First variants, then parents
                            if (!empty($batch)) {
                                $payload[] = [
                                    'action' => 'upsert',
                                    'entity' => 'product',
                                    'payload' => array_values($batch),
                                ];
                            }
                            if (!empty($this->parentsToUpdate)) {
                                $payload[] = [
                                    'action' => 'upsert',
                                    'entity' => 'product',
                                    'payload' => array_values($this->parentsToUpdate),
                                ];
                            }
                            $this->shopwareApiService->sync($payload);

                            $results['upserted'] += count($batch);
                            $parentCount = count($this->parentsToUpdate);
                            if ($parentCount > 0) {
                                $this->pushDetail($results, 'Bulk upserted parents: ' . $parentCount);
                            }
                            $this->pushDetail($results, 'Bulk upserted variants: ' . count($batch));
                        } catch (\Throwable $t) {
                            $msg = 'Bulk upsert failed for chunk: ' . $t->getMessage();
                            Log::error($msg);
                            $results['errors'][] = $msg;
                        }
                        // clear batch and parents to free memory
                        $batch = [];
                        $this->parentsToUpdate = [];
                    }

                    unset($chunk);
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                });

            if (!$hadAny) {
                return ['success' => true, 'message' => 'No products to sync.'];
            }

            $results['success'] = true;
            if ($this->useBulk) {
                $results['message'] = sprintf(
                    'Variant sync completed (bulk). Upserted: %d, Skipped: %d, Errors: %d',
                    $results['upserted'],
                    $results['skipped'],
                    count($results['errors'])
                );
            } else {
                $results['message'] = sprintf(
                    'Variant sync completed. Created: %d, Updated: %d, Skipped: %d, Errors: %d',
                    $results['created'],
                    $results['updated'],
                    $results['skipped'],
                    count($results['errors'])
                );
            }

            return $results;
        } catch (\Exception $e) {
            $errorMsg = 'Product variant sync failed: ' . $e->getMessage();
            Log::error($errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
                'errors' => [$errorMsg]
            ];
        }
    }

    protected function resolveParentProductId(SWProduct $product): ?string
    {
        $productClass = $product->productClass;
        if (!$productClass) {
            return null;
        }

        $source = $productClass->productnumber ?: $productClass->title;
        if (!$source) {
            return null;
        }
        return ShopwareApiHelper::generateId($source);
    }

    protected function ensureVariantOptions(SWProduct $product): array
    {
        // Assumption: VariantSyncController has already imported property groups and options to Shopware
        // So we only compute the deterministic IDs that match those entities.
        $optionIds = [];

        $headers = $product->productClass?->variantHeaders?->sortBy('pos')->values() ?? collect();
        $values = $product->variantValues?->sortBy('pos')->values() ?? collect();

        $count = min($headers->count(), $values->count());
        for ($i = 0; $i < $count; $i++) {
            $header = $headers[$i];
            $value = $values[$i];

            $headerName = trim((string) ($header->title ?? ''));
            $valueName = trim((string) ($value->value ?? ''));
            if ($headerName === '' || $valueName === '') {
                // skip invalid pairs silently; caller will handle missing options gracefully
                continue;
            }

            // Deterministic IDs consistent with VariantSyncController
            $groupId = ShopwareApiHelper::generateId($headerName);
            $deterministicOptionId = ShopwareApiHelper::generateId($groupId . '|' . $valueName);

            // Prefer cached SW ID if present (created/found by VariantSyncController)
            $cached = SWVariantCache::where('header', $headerName)->where('value', $valueName)->value('sw_id');
            $optionId = $cached ?: $deterministicOptionId;

            // No API calls here, just push option id to use on the product variant
            $optionIds[] = $optionId;
        }

        return $optionIds;
    }

    protected function ensureParentConfiguratorSettings(?SWProductClass $productClass, string $parentId, array $optionIds): void
    {
        if (!$productClass) return;

        // Do NOT early return here; we want to accumulate option IDs across multiple variants of the same parent
        // if (isset($this->updatedParents[$parentId])) {
        //     return;
        // }

        // Use actual option IDs that occur on variants, and reference them to avoid creating blank options
        if (empty($optionIds)) return;

        // Initialize tracking for this parent
        if (!isset($this->parentConfiguratorOptionIds[$parentId])) {
            $this->parentConfiguratorOptionIds[$parentId] = [];
        }

        if (!isset($this->parentsToUpdate[$parentId])) {
            $this->parentsToUpdate[$parentId] = [
                'id' => $parentId,
                'configuratorSettings' => [],
            ];
        }

        foreach ($optionIds as $oid) {
            if (!isset($this->parentConfiguratorOptionIds[$parentId][$oid])) {
                $this->parentsToUpdate[$parentId]['configuratorSettings'][] = [
                    // Use direct reference to avoid groupId validation on nested entity
                    'optionId' => $oid,
                ];
                $this->parentConfiguratorOptionIds[$parentId][$oid] = true;
            }
        }

        // Mark parent as prepared to avoid re-initializing for every variant in the same run
        $this->updatedParents[$parentId] = true;
    }

    protected function prepareVariantProductData(SWProduct $product, string $parentId, array $optionIds): array
    {
        $taxId = config('app.shopware_default_tax');
        $currencyId = config('app.shopware_default_currency');
        $manufacturerId = config('app.shopware_default_manufacturer');

        if (!$taxId) {
            throw new \RuntimeException('Missing config: app.shopware_default_tax_id');
        }
        if (!$currencyId) {
            throw new \RuntimeException('Missing config: app.shopware_default_currency_id');
        }

        $productClass = $product->productClass;

        $idSource = $product->articlenumber ?: ($productClass->title ?? (string) $product->id);
        $nameBase = $product->serie ?: $product->articlenumber;
        $name = trim(($productClass->title ?? '') . ' ' . ($nameBase ?? '')) ?: $product->articlenumber;

        $gross = 0.0;
        $net = 0.0;
        if (!is_null($product->price) && is_numeric($product->price)) {
            $gross = (float) $product->price;
            $net = $gross; // adjust if you have tax calculation
        }

        $data = [
            'id' => $this->shopwareApiHelper->generateId($idSource),
            'parentId' => $parentId,
            'productNumber' => $product->articlenumber,
            'active' => (bool) ($product->sw_active ?? true),
            'name' => $name,
            'stock' => 0,
            'manufacturerId' => $manufacturerId,
            'taxId' => $taxId,
            'price' => [[
                'currencyId' => $currencyId,
                'gross' => $gross,
                'net' => $net,
                'linked' => false
            ]],
            'options' => array_map(fn ($id) => ['id' => $id], $optionIds),
        ];

        return $data;
    }

    protected function pushDetail(array &$results, string $message): void
    {
        if (count($results['details']) < $this->maxDetails) {
            $results['details'][] = $message;
        }
    }
}
