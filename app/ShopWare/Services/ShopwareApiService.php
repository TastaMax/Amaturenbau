<?php

namespace App\ShopWare\Services;

class ShopwareApiService {

    protected ApiClientShopware $client;

    public function __construct(ApiClientShopware $client)
    {
        $this->client = $client;
    }

    // ================== PRODUKTE ==================

    public function searchProducts(array $criteria = []): array
    {
        return $this->client->post('api/search/product', $criteria);
    }

    public function getProduct(string $id, array $associations = []): array
    {
        $params = empty($associations) ? [] : ['associations' => $associations];
        return $this->client->get("api/product/{$id}", $params);
    }

    public function createProduct(array $productData): array
    {
        return $this->client->post('api/product', $productData);
    }

    public function updateProduct(string $id, array $productData): array
    {
        return $this->client->patch("api/product/{$id}", $productData);
    }

    public function deleteProduct(string $id): array
    {
        return $this->client->delete("api/product/{$id}");
    }

    // ================== KATEGORIEN ==================

    public function searchCategories(array $criteria = []): array
    {
        return $this->client->post('api/search/category', $criteria);
    }

    public function getCategory(string $id): array
    {
        return $this->client->get("api/category/{$id}");
    }

    public function createCategory(array $categoryData): array
    {
        return $this->client->post('api/category?_response=true', $categoryData);
    }

    public function updateCategory(string $id, array $categoryData): array
    {
        return $this->client->patch("api/category/{$id}", $categoryData);
    }

    public function deleteCategory(string $id): array
    {
        return $this->client->delete("api/category/{$id}");
    }

    // ================== MEDIA ==================

    public function createMedia(?string $id = null, array $data = []): array
    {
        if ($id) {
            $data['id'] = $id;
        }
        return $this->client->post('api/media', $data);
    }

    /**
     * Uploads a media from a remote URL into an existing media entity.
     * Shopware endpoint expects extension and fileName as query params and a JSON body with { url }.
     */
    public function uploadMediaFromUrl(string $mediaId, string $url, string $fileName, string $extension): array
    {
        $qs = http_build_query(['extension' => $extension, 'fileName' => $fileName]);
        return $this->client->post("api/_action/media/{$mediaId}/upload?{$qs}", [
            'url' => $url,
        ]);
    }

    /**
     * Assign media to product via product update. Provide full media array and optional cover.
     */
    public function upsertProductMedia(string $productId, array $mediaItems, ?string $coverMediaId = null): array
    {
        $payload = [
            'id' => $productId,
            'media' => $mediaItems,
        ];
        if ($coverMediaId) {
            $payload['cover'] = ['mediaId' => $coverMediaId];
        }
        return $this->updateProduct($productId, $payload);
    }

    /**
     * Search media by criteria.
     */
    public function searchMedia(array $criteria = []): array
    {
        return $this->client->post('api/search/media', $criteria);
    }

    /**
     * Bulk upsert media entities via sync.
     * Each item should at least contain ['id' => <32-hex>, 'mediaFolderId' => <32-hex>].
     */
    public function bulkUpsertMedia(array $media): array
    {
        $payload = [[
            'action' => 'upsert',
            'entity' => 'media',
            'payload' => array_values($media),
        ]];
        return $this->sync($payload);
    }

    /**
     * Bulk delete media by IDs via sync.
     */
    public function bulkDeleteMediaByIds(array $ids): array
    {
        $payload = [[
            'action' => 'delete',
            'entity' => 'media',
            'payload' => array_map(fn($id) => ['id' => $id], $ids),
        ]];
        return $this->sync($payload);
    }

    // ================== KUNDEN ==================

    public function searchCustomers(array $criteria = []): array
    {
        return $this->client->post('api/search/customer', $criteria);
    }

    public function getCustomer(string $id): array
    {
        return $this->client->get("api/customer/{$id}");
    }

    public function createCustomer(array $customerData): array
    {
        return $this->client->post('api/customer', $customerData);
    }

    public function updateCustomer(string $id, array $customerData): array
    {
        return $this->client->patch("api/customer/{$id}", $customerData);
    }

    // ================== EIGENSCHAFTEN ==================

    public function searchPropertyGroups(array $criteria = []): array
    {
        return $this->client->post('api/search/property-group', $criteria);
    }

    public function getPropertyGroup(string $id): array
    {
        return $this->client->get("api/property-group/{$id}");
    }

    public function createPropertyGroup(array $data): array
    {
        return $this->client->post('api/property-group', $data);
    }

    public function updatePropertyGroup(string $id, array $data): array
    {
        return $this->client->patch("api/property-group/{$id}", $data);
    }

    public function searchPropertyGroupOptions(string $groupId, array $criteria = []): array
    {
        return $this->client->post("api/search/property-group/{$groupId}/options", $criteria);
    }

    public function getPropertyGroupOption(string $id): array
    {
        return $this->client->get("api/property-group-option/{$id}");
    }

    public function createPropertyGroupOption(array $data): array
    {
        return $this->client->post('api/property-group-option', $data);
    }

    public function updatePropertyGroupOption(string $id, array $data): array
    {
        return $this->client->patch("api/property-group-option/{$id}", $data);
    }

    // ================== SYSTEM-INFORMATIONEN ==================

    public function getLanguages(): array
    {
        return $this->client->get('api/locale');
    }

    public function getCurrencies(): array
    {
        return $this->client->get('api/currency');
    }

    public function getTaxes(): array
    {
        return $this->client->get('api/tax');
    }

    public function getManufacturers(): array
    {
        return $this->client->get('api/product-manufacturer');
    }

    public function getSalesChannels(): array
    {
        return $this->client->get('api/sales-channel');
    }

    public function updateSalesChannel(string $id, array $data): array
    {
        return $this->client->patch("api/sales-channel/{$id}", $data);
    }

    // ================== SPEZIELLE SHOPWARE FUNKTIONEN ==================

    /**
     * Sucht eine Property Group nach Namen
     */
    public function findPropertyGroupByName(string $name): array
    {
        return $this->searchPropertyGroups([
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'name',
                    'value' => $name
                ]
            ],
            'limit' => 1
        ]);
    }

    /**
     * Sucht Property Group Options nach Namen
     */
    public function findPropertyGroupOptionsByName(string $groupId, string $name): array
    {
        return $this->searchPropertyGroupOptions($groupId, [
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'name',
                    'value' => $name
                ]
            ]
        ]);
    }

    /**
     * Erstellt Property Group mit englischer Übersetzung
     */
    public function createPropertyGroupWithTranslation(string $name, string $nameEn, ?string $id = null): array
    {
        $data = [
            'name' => $name,
            'displayType' => 'dropdown',
            'translations' => [
                'en-GB' => [
                    'name' => $nameEn
                ]
            ]
        ];

        if ($id) {
            $data['id'] = $id;
        }

        return $this->createPropertyGroup($data);
    }

    /**
     * Erstellt Property Group Option mit englischer Übersetzung
     */
    public function createPropertyGroupOptionWithTranslation(string $groupId, string $name, string $nameEn, int $position = 1, ?string $id = null): array
    {
        $data = [
            'groupId' => $groupId,
            'name' => $name,
            'position' => $position,
            'translations' => [
                'en-GB' => [
                    'name' => $nameEn
                ]
            ]
        ];

        if ($id) {
            $data['id'] = $id;
        }

        return $this->createPropertyGroupOption($data);
    }

    /**
     * Erstellt oder aktualisiert eine Property Group
     */
    public function upsertPropertyGroup(array $data): array
    {
        $existingGroup = $this->findPropertyGroupByName($data['name']);

        if (!empty($existingGroup)) {
            return $this->updatePropertyGroup($existingGroup[0]['id'], $data);
        }

        return $this->createPropertyGroup($data);
    }

    /**
     * Erstellt oder aktualisiert eine Property Group Option
     */
    public function upsertPropertyGroupOption(array $data): array
    {
        $existingOption = $this->findPropertyGroupOptionsByName($data['groupId'], $data['name']);

        if (!empty($existingOption)) {
            return $this->updatePropertyGroupOption($existingOption[0]['id'], $data);
        }

        return $this->createPropertyGroupOption($data);
    }

    // ================== BULK SYNC (/_action/sync) ==================

    /**
     * Calls Shopware's bulk sync endpoint.
     * Payload format: [ { action: 'upsert'|'delete', entity: 'product', payload: [ {...}, ... ] }, ... ]
     */
    public function sync(array $payload): array
    {
        return $this->client->post('api/_action/sync', $payload);
    }

    /**
     * Convenience: bulk upsert products in one call. Will upsert by provided IDs/productNumbers.
     */
    public function bulkUpsertProducts(array $products): array
    {
        $payload = [[
            'action' => 'upsert',
            'entity' => 'product',
            'payload' => array_values($products),
        ]];

        return $this->sync($payload);
    }

    /**
     * Convenience: bulk upsert products (including media associations) via sync.
     */
    public function bulkUpsertProductsMedia(array $products): array
    {
        $payload = [[
            'action' => 'upsert',
            'entity' => 'product',
            'payload' => array_values($products),
        ]];
        return $this->sync($payload);
    }
}
