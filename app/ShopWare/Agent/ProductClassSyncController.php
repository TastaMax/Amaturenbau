<?php

namespace App\ShopWare\Agent;

use App\Http\Controllers\Controller;
use App\Models\SWProductClass;
use App\ShopWare\Services\ShopwareApiService;
use App\ShopWare\Services\ShopwareApiHelper;
use Illuminate\Support\Facades\Log;

class ProductClassSyncController extends Controller
{
    protected ShopwareApiService $shopwareApiService;
    protected ShopwareApiHelper $shopwareApiHelper;
    protected mixed $defaultParentId;
    protected  mixed $defaultTaxId;

    public function __construct(ShopwareApiService $shopwareApiService, ShopwareApiHelper $shopwareApiHelper)
    {
        $this->shopwareApiService = $shopwareApiService;
        $this->shopwareApiHelper = $shopwareApiHelper;
        $this->defaultParentId = config('app.shopware_default_category');
    }

    public function syncProductClasses()
    {
        try {
            $localClasses = SWProductClass::with('subCategory.category')->get();

            if ($localClasses->isEmpty()) {
                return ['success' => true, 'message' => 'No product classes to sync.'];
            }

            $results = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => [],
                'details' => []
            ];

            foreach ($localClasses as $productClass) {
                try {
                    if (empty($productClass->productnumber)) {
                        $results['skipped']++;
                        $results['details'][] = "Skipped: missing productnumber for product class ID {$productClass->id}";
                        continue;
                    }

                    // Get SubCategory ShopWare ID via hash
                    $subCategoryShopwareId = null;
                    if ($productClass->subCategory) {
                        $subCategoryShopwareId = $this->shopwareApiHelper->generateId($productClass->subCategory->title);
                    }

                    $productData = $this->prepareProductData($productClass, $subCategoryShopwareId);

                    // Find existing product by productNumber
                    $existingProduct = $this->findProductByNumber($productClass->productnumber);

                    if ($existingProduct) {
                        $this->shopwareApiService->updateProduct($existingProduct['id'], $productData);
                        $results['updated']++;
                        $results['details'][] = "Updated product: {$productClass->title} ({$productClass->productnumber})";
                    } else {
                        $response = $this->shopwareApiService->createProduct($productData);
                        $results['created']++;
                        $createdId = $response['data']['data']['id'] ?? null;
                        $results['details'][] = "Created product: {$productClass->title} ({$productClass->productnumber})" . ($createdId ? " (ID: {$createdId})" : '');
                    }
                } catch (\Exception $e) {
                    $errorMsg = "Failed to sync product from product class '{$productClass->title}' (ID: {$productClass->id}): " . $e->getMessage();
                    Log::error($errorMsg);
                    $results['errors'][] = $errorMsg;
                }
            }

            $results['success'] = true;
            $results['message'] = sprintf(
                'Sync completed. Created: %d, Updated: %d, Skipped: %d, Errors: %d',
                $results['created'],
                $results['updated'],
                $results['skipped'],
                count($results['errors'])
            );

            return $results;
        } catch (\Exception $e) {
            $errorMsg = 'Product class sync failed: ' . $e->getMessage();
            Log::error($errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
                'errors' => [$errorMsg]
            ];
        }
    }

    protected function findProductByNumber(string $productNumber): ?array
    {
        try {
            $response = $this->shopwareApiService->searchProducts([
                'filter' => [
                    ['field' => 'productNumber', 'type' => 'equals', 'value' => $productNumber]
                ],
                'limit' => 1
            ]);

            return $response['data']['data'][0] ?? null;
        } catch (\Exception $e) {
            Log::warning("Failed to find product by number '{$productNumber}': " . $e->getMessage());
            return null;
        }
    }

    protected function prepareProductData(SWProductClass $productClass, ?string $subCategoryId = null): array
    {
        $taxId = config('app.shopware_default_tax');
        $currencyId = config('app.shopware_default_currency');
        $manufacturerId = config('app.shopware_default_manufacturer');
        $salesChannelId = config('app.shopware_sales_channel_id');

        if (!$taxId) {
            throw new \RuntimeException('Missing config: app.shopware_default_tax_id');
        }
        if (!$currencyId) {
            throw new \RuntimeException('Missing config: app.shopware_default_currency_id');
        }

        $idSource = $productClass->productnumber ?: $productClass->title;

        $data = [
            'id' => $this->shopwareApiHelper->generateId($idSource),
            'productNumber' => $productClass->productnumber,
            'active' => (bool) ($productClass->sw_active ?? true),
            'name' => $productClass->title,
            'description' => $productClass->description,
            'stock' => 0,
            'manufacturerId' => $manufacturerId,
            'taxId' => $taxId,
            'price' => [[
                'currencyId' => $currencyId,
                'gross' => 0,
                'net' => 0,
                'linked' => false
            ]],
            'translations' => [
                'en-GB' => [
                    'name' => $productClass->title_en ?? $productClass->title,
                    'description' => $productClass->description_en ?? $productClass->description
                ]
            ]
        ];

        // Sichtbarkeit für den definierten Verkaufskanal aktivieren
        if ($salesChannelId) {
            $data['visibilities'] = [[
                'productId' => $this->shopwareApiHelper->generateId($idSource),
                'salesChannelId' => $salesChannelId,
                'visibility' => 30 // all: Suche + Kategorien + Detailseite
            ]];
        }

        // Assign to SubCategory if available
        if ($subCategoryId) {
            $data['categories'] = [[
                'id' => $subCategoryId
            ]];
        }

        return $data;
    }
}
