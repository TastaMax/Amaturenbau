<?php

namespace App\ShopWare\Agent;

use App\Http\Controllers\Controller;
use App\Models\SWProductClass;
use App\Models\SWVariantCache;
use App\ShopWare\Services\ShopwareApiService;
use App\ShopWare\Services\ShopwareApiHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class VariantSyncController extends Controller
{
    protected ShopwareApiService $shopwareApiService;
    protected ShopwareApiHelper $shopwareApiHelper;
    // In-memory caches for this request
    protected array $groupCache = [];        // [groupName => groupId]
    protected array $optionCache = [];       // [groupId|value => optionId]

    public function __construct(ShopwareApiService $shopwareApiService, ShopwareApiHelper $shopwareApiHelper)
    {
        $this->shopwareApiService = $shopwareApiService;
        $this->shopwareApiHelper = $shopwareApiHelper;
    }

    public function syncVariants(Request $request)
    {
        // Always clear in-memory caches before each sync run
        $this->resetCaches();

        $dbCachePurged = true;
        // Optional: allow purging the persistent DB cache via query param ?purge_db_cache=1
        if ($dbCachePurged) {
            SWVariantCache::truncate();
        }

        $results = [
            'groups_created' => 0,
            'groups_found' => 0,
            'options_created' => 0,
            'options_found' => 0,
            'cached' => 0,
            'errors' => [],
            'details' => []
        ];

        try {
            // Load product classes with their headers and products->variantValues
            $productClasses = SWProductClass::with(['variantHeaders', 'products.variantValues'])->get();

            if ($productClasses->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Keine Daten zum Synchronisieren gefunden.',
                    'db_cache_purged' => $dbCachePurged,
                ];
            }

            foreach ($productClasses as $productClass) {
                $headersByPos = $productClass->variantHeaders->keyBy('pos');

                foreach ($productClass->products as $product) {
                    foreach ($product->variantValues as $variantValue) {
                        $header = $headersByPos->get($variantValue->pos);
                        if (!$header) {
                            $msg = "Keine Header-Zuordnung für pos={$variantValue->pos} in Klasse {$productClass->id} gefunden.";
                            Log::warning($msg);
                            $results['details'][] = $msg;
                            continue;
                        }

                        $headerName = $header->title;
                        $headerNameEn = $header->title_en ?: $headerName;
                        $valueName = trim((string) $variantValue->value);
                        $valueNameEn = trim((string) ($variantValue->value_en ?: $valueName));

                        if($headerName == '') {
                            $err = "Property Group Name konnte nicht gefunden werden '{$headerName}'.";
                            Log::error($err);
                            $results['errors'][] = $err;
                            continue;
                        }

                        if($valueName == '') {
                            $err = "Property Group Value konnte nicht gefunden werden '{$headerName}'.";
                            Log::error($err);
                            $results['errors'][] = $err;
                            continue;
                        }

                        // Ensure Property Group exists
                        $groupId = $this->ensurePropertyGroup($headerName, $headerNameEn, $results);
                        if (!$groupId) {
                            $err = "Property Group konnte nicht ermittelt werden für '{$headerName}'.";
                            Log::error($err);
                            $results['errors'][] = $err;
                            continue;
                        }

                        // Ensure Property Option exists
                        $optionId = $this->ensurePropertyOption($groupId, $valueName, $valueNameEn, $headerName, $results);
                        if (!$optionId) {
                            $err = "Property Option konnte nicht ermittelt werden für '{$headerName}' => '{$valueName}'.";
                            Log::error($err);
                            $results['errors'][] = $err;
                            continue;
                        }

                        // Persist DB cache mapping for resolved option
                        SWVariantCache::updateOrInsert(
                            ['header' => $headerName, 'value' => $valueName],
                            ['sw_id' => $optionId]
                        );
                    }
                }
            }

            $results['success'] = true;
            $results['message'] = sprintf(
                'Eigenschaften Sync abgeschlossen. Gruppen neu: %d, vorhanden: %d, Optionen neu: %d, vorhanden: %d, Cache-Treffer: %d, Fehler: %d',
                $results['groups_created'],
                $results['groups_found'],
                $results['options_created'],
                $results['options_found'],
                $results['cached'],
                count($results['errors'])
            );
            $results['db_cache_purged'] = $dbCachePurged;
            return $results;

        } catch (\Throwable $e) {
            $errorMsg = 'Eigenschaften Synchronisierung fehlgeschlagen: ' . $e->getMessage();
            Log::error($errorMsg, ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }
    }

    protected function resetCaches(): void
    {
        $this->groupCache = [];
        $this->optionCache = [];
    }

    protected function ensurePropertyGroup(string $name, string $nameEn, array &$results): ?string
    {
        try {
            // In-memory cache check by group name
            if (isset($this->groupCache[$name])) {
                $results['groups_found']++;
                return $this->groupCache[$name];
            }

            $search = $this->shopwareApiService->findPropertyGroupByName($name);
            $found = $search['data']['data'][0] ?? null;
            if ($found) {
                $results['groups_found']++;
                $this->groupCache[$name] = $found['id'];
                return $found['id'];
            }

            // Create with deterministic ID
            $id = ShopwareApiHelper::generateId($name);
            $create = $this->shopwareApiService->createPropertyGroupWithTranslation($name, $nameEn, $id);
            if (!($create['success'] ?? false)) {
                $results['errors'][] = "Fehler beim Erstellen der Property Group '{$name}': " . json_encode($create['error'] ?? []);
                return null;
            }
            $results['groups_created']++;
            $this->groupCache[$name] = $id;
            return $id;
        } catch (\Throwable $e) {
            $results['errors'][] = "Exception Property Group '{$name}': " . $e->getMessage();
            return null;
        }
    }

    protected function ensurePropertyOption(string $groupId, string $value, string $valueEn, string $headerName, array &$results): ?string
    {
        try {
            $cacheKey = $groupId . '|' . $value;
            // Fast-path: exact option cache
            if (isset($this->optionCache[$cacheKey])) {
                $results['options_found']++;
                // ensure DB cache is persisted as well
                SWVariantCache::updateOrInsert(
                    ['header' => $headerName, 'value' => $value],
                    ['sw_id' => $this->optionCache[$cacheKey]]
                );
                return $this->optionCache[$cacheKey];
            }
            // Secondary cache from DB warming using header name is not exact; still avoid duplicate creations within single run
            $secondaryKey = $headerName . '|*|' . $value;
            if (isset($this->optionCache[$secondaryKey])) {
                $results['options_found']++;
                // Persist DB cache (it should already exist, but ensure upsert)
                SWVariantCache::updateOrInsert(
                    ['header' => $headerName, 'value' => $value],
                    ['sw_id' => $this->optionCache[$secondaryKey]]
                );
            }

            $search = $this->shopwareApiService->findPropertyGroupOptionsByName($groupId, $value);
            $found = $search['data']['data'][0] ?? null;
            if ($found) {
                $results['options_found']++;
                $this->optionCache[$cacheKey] = $found['id'];
                // Persist DB cache mapping
                SWVariantCache::updateOrInsert(
                    ['header' => $headerName, 'value' => $value],
                    ['sw_id' => $found['id']]
                );
                return $found['id'];
            }

            // Create with deterministic ID combining group and value
            $id = ShopwareApiHelper::generateId($groupId . '|' . $value);
            $create = $this->shopwareApiService->createPropertyGroupOptionWithTranslation($groupId, $value, $valueEn, 1, $id);
            if (!($create['success'] ?? false)) {
                $results['errors'][] = "Fehler beim Erstellen der Option '{$headerName}' => '{$value}': " . json_encode($create['error'] ?? []);
                return null;
            }
            $results['options_created']++;
            $this->optionCache[$cacheKey] = $id;
            // Persist DB cache mapping
            SWVariantCache::updateOrInsert(
                ['header' => $headerName, 'value' => $value],
                ['sw_id' => $id]
            );
            return $id;
        } catch (\Throwable $e) {
            $results['errors'][] = "Exception Option '{$headerName}' => '{$value}': " . $e->getMessage();
            return null;
        }
    }
}
