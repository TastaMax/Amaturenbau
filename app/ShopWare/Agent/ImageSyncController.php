<?php

namespace App\ShopWare\Agent;

use App\Http\Controllers\Controller;
use App\Models\SWPicture;
use App\Models\SWProduct;
use App\Models\SWProductClass;
use App\ShopWare\Services\ShopwareApiService;
use App\ShopWare\Services\ShopwareApiHelper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ImageSyncController extends Controller
{
    protected ShopwareApiService $shopwareApiService;
    protected ShopwareApiHelper $shopwareApiHelper;

    public function __construct(ShopwareApiService $shopwareApiService, ShopwareApiHelper $shopwareApiHelper)
    {
        $this->shopwareApiService = $shopwareApiService;
        $this->shopwareApiHelper = $shopwareApiHelper;
    }

    /**
     * Sync images from SWPicture to Shopware products.
     * type: 1 => product class (parent), 0 => product variant
     * assignment_id: local DB id of SWProductClass or SWProduct
     */
    public function syncImages(): array
    {
        $results = [
            'processed' => 0,
            'uploaded' => 0,
            'assigned' => 0,
            'skipped' => 0,
            'errors' => [],
            'details' => [],
        ];

        try {
            // Resolve and validate target media folder (must be a Shopware ID)
            $mediaFolder = (string) (Config::get('app.shopware_default_media_folder') ?? '');
            if (!$mediaFolder || !ShopwareApiHelper::isValidId($mediaFolder)) {
                return [
                    'success' => false,
                    'message' => 'Konfiguration fehlerhaft: app.shopware_default_media_folder muss eine gültige Shopware-ID (32-hex) sein.'
                ];
            }

            // 1) Cleanup: delete all media in the configured folder (batched)
            $deleteLimit = 500;
            $page = 1;
            do {
                $search = $this->shopwareApiService->searchMedia([
                    'filter' => [[
                        'type' => 'equals',
                        'field' => 'mediaFolderId',
                        'value' => $mediaFolder,
                    ]],
                    'limit' => $deleteLimit,
                    'page' => $page,
                ]);

                $data = $search['data'] ?? ($search[0]['data'] ?? []); // support possible client response shapes
                $ids = [];
                foreach ($data as $row) {
                    if (!empty($row['id'])) {
                        $ids[] = $row['id'];
                    } elseif (!empty($row['attributes']['id'])) {
                        $ids[] = $row['attributes']['id'];
                    }
                }

                if (!empty($ids)) {
                    $del = $this->shopwareApiService->bulkDeleteMediaByIds($ids);
                    if (!($del['success'] ?? true)) {
                        $results['errors'][] = 'Fehler beim Löschen von Medien im Zielordner: ' . json_encode($del);
                    } else {
                        $results['details'][] = 'Gelöscht: ' . count($ids) . ' Medien im Zielordner.';
                    }
                }

                $page++;
                $more = !empty($ids) && count($ids) === $deleteLimit; // continue if page was full
            } while ($more);

            $batchSize = 1000; // chunk size for large datasets

            $totalCount = 0;
            SWPicture::orderBy('assignment_id')->orderBy('pos')->chunk($batchSize, function ($pictures) use (&$results, $mediaFolder, &$totalCount) {
                if ($pictures->isEmpty()) {
                    return false;
                }

                $totalCount += $pictures->count();

                // Group pictures by target Shopware product id
                $grouped = [];
                $mediaById = []; // unique media entities to upsert via sync first (id => ['id'=>..,'mediaFolderId'=>..])

                foreach ($pictures as $picture) {
                    $results['processed']++;

                    $type = (int) ($picture->type ?? 0);
                    $assignmentId = (int) ($picture->assignment_id ?? 0);
                    $fileNameRaw = (string) ($picture->file ?? '');
                    $pos = (int) ($picture->pos ?? 0);

                    if ($assignmentId <= 0 || $fileNameRaw === '') {
                        $results['skipped']++;
                        $results['details'][] = "Skipped: invalid record id={$picture->id} (assignment or file missing)";
                        continue;
                    }

                    // Build remote URL
                    $urlBase = $type === 1
                        ? 'http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/pictures/'
                        : 'http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/product_pictures/';
                    $remoteUrl = $urlBase . ltrim($fileNameRaw, '/');

                    // Resolve target Shopware product id
                    $targetProductId = $this->resolveShopwareProductId($type, $assignmentId);
                    if (!$targetProductId) {
                        $results['skipped']++;
                        $results['details'][] = "Skipped: could not resolve Shopware product id for picture id={$picture->id} (type={$type}, assignment={$assignmentId})";
                        continue;
                    }

                    // Deterministic media id based on full url
                    $mediaId = $this->shopwareApiHelper->generateId($remoteUrl);
                    // collect media entity for upsert
                    $mediaById[$mediaId] = ['id' => $mediaId, 'mediaFolderId' => $mediaFolder];

                    // Bucket by product
                    $grouped[$targetProductId] = $grouped[$targetProductId] ?? [];
                    $grouped[$targetProductId][] = [
                        'url' => $remoteUrl,
                        'file' => $fileNameRaw,
                        'pos' => $pos,
                        'mediaId' => $mediaId,
                    ];
                }

                // For each product, prepare assignments
                $productsPayload = [];
                foreach ($grouped as $productId => $items) {
                    // ensure consistent ordering by pos
                    usort($items, fn($a, $b) => $a['pos'] <=> $b['pos']);

                    $mediaAssignments = [];
                    $coverMediaId = null;

                    foreach ($items as $idx => $it) {
                        $fileName = $this->extractFileName($it['file']);
                        $extension = $this->extractExtension($fileName) ?: 'jpg';

                        $mediaId = $it['mediaId'];

                        $mediaAssignments[] = [
                            'mediaId' => $mediaId,
                            'position' => $it['pos'] ?? $idx,
                        ];

                        if ($coverMediaId === null) {
                            $coverMediaId = $mediaId;
                        }
                    }

                    if (!empty($mediaAssignments)) {
                        $productsPayload[$productId] = [
                            'id' => $productId,
                            'media' => $mediaAssignments,
                        ];
                        if ($coverMediaId) {
                            $productsPayload[$productId]['cover'] = ['mediaId' => $coverMediaId];
                        }
                    }
                }

                // 3) Single combined sync: media upsert + product media assignment in one request
                if (!empty($mediaById) || !empty($productsPayload)) {
                    $payload = [];
                    if (!empty($mediaById)) {
                        $payload[] = [
                            'action' => 'upsert',
                            'entity' => 'media',
                            'payload' => array_values($mediaById),
                        ];
                    }
                    if (!empty($productsPayload)) {
                        $payload[] = [
                            'action' => 'upsert',
                            'entity' => 'product',
                            'payload' => array_values($productsPayload),
                        ];
                    }
                    $syncRes = $this->shopwareApiService->sync($payload);
                    if (!($syncRes['success'] ?? true)) {
                        $results['errors'][] = 'Kombinierter Sync (Media+Produkte) fehlgeschlagen: ' . json_encode($syncRes);
                    } else {
                        foreach ($productsPayload as $pp) {
                            $results['assigned'] += isset($pp['media']) ? count($pp['media']) : 0;
                        }
                        $results['details'][] = 'Batch: ' . count($mediaById) . ' Medien + ' . count($productsPayload) . ' Produkt(e) per Sync verarbeitet.';
                    }
                }

                // 4) Upload media files from URLs (cannot be batched via sync)
                foreach ($grouped as $productId => $items) {
                    foreach ($items as $idx => $it) {
                        $fileName = $this->extractFileName($it['file']);
                        $extension = $this->extractExtension($fileName) ?: 'jpg';
                        $mediaId = $it['mediaId'];
                        $upload = $this->shopwareApiService->uploadMediaFromUrl($mediaId, $it['url'], pathinfo($fileName, PATHINFO_FILENAME), $extension);
                        if (!($upload['success'] ?? false)) {
                            $results['errors'][] = "Media upload failed for {$fileName}: " . json_encode($upload['error'] ?? []);
                            continue;
                        }
                        $results['uploaded']++;
                    }
                }
            });

            if ($results['processed'] === 0) {
                return ['success' => true, 'message' => 'Keine Bilder zu synchronisieren.'];
            }

            $results['success'] = true;
            $results['message'] = sprintf('Images sync done. Processed: %d, Uploaded: %d, Assigned: %d, Skipped: %d, Errors: %d',
                $results['processed'], $results['uploaded'], $results['assigned'], $results['skipped'], count($results['errors'])
            );
            return $results;
        } catch (\Throwable $e) {
            $msg = 'Image sync failed: ' . $e->getMessage();
            Log::error($msg, ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => $msg
            ];
        }
    }

    protected function resolveShopwareProductId(int $type, int $assignmentId): ?string
    {
        // type: 1 => product class (parent), 0 => product variant
        if ($type === 0) {
            $product = SWProduct::find($assignmentId);
            if (!$product) return null;

            $productClass = $product->productClass;
            $idSource = $product->articlenumber ?: ($productClass->title ?? (string) $product->id);
            return $this->shopwareApiHelper->generateId($idSource);
        }

        // parent/class
        $cls = SWProductClass::find($assignmentId);
        if (!$cls) return null;
        $source = $cls->productnumber ?: $cls->title;
        if (!$source) return null;
        return $this->shopwareApiHelper->generateId($source);
    }

    protected function extractFileName(string $filePath): string
    {
        $base = basename($filePath);
        return $base ?: $filePath;
    }

    protected function extractExtension(string $fileName): ?string
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return $ext ? strtolower($ext) : null;
    }
}
