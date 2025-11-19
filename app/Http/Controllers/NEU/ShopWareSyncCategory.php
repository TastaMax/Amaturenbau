<?php

namespace App\Http\Controllers\NEU;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopWareSyncCategory
{
    protected ShopWareAPIController $api;
    protected ShopWareHelper $swHelper;

    public function __construct()
    {
        $this->api = new ShopWareAPIController();
        $this->swHelper = new ShopWareHelper();
    }


    public function bulkCreateCategories(array $categoryData, bool $forceSync = false): array
    {
        $payload = [];

        foreach ($categoryData as $category) {
            $categoryId = $category['sw_id'] ?? $this->swHelper->generateUUID();

            // Hauptkategorien verarbeiten
            $categoryPayload = [
                'id' => $categoryId,
                'parentId' => $this->swHelper->getDefaultCategory(), // WICHTIG: Default Shopware Root Category
                'name' => $category['title'],
                'translations' => [
                    'en-GB' => [
                        'name' => $category['title_en'] ?? $category['title'],
                    ],
                ],
            ];

            // Unterkategorien verarbeiten
            if (!empty($category['subcategories'])) {
                $children = [];

                foreach ($category['subcategories'] as $sub) {
                    $children[] = [
                        'id' => $sub['sw_id'] ?? $this->swHelper->generateUUID(),
                        'parentId' => $categoryId,
                        'name' => $sub['title'],
                        'translations' => [
                            'en-GB' => [
                                'name' => $sub['title_en'] ?? $sub['title'],
                            ],
                        ],
                    ];
                }

                $categoryPayload['parent'] = $children;
            }

            $payload[] = $categoryPayload;
        }

        dd($payload);

        // POST-Request an Shopware via API Controller
        $response = $this->api->makeRequest('POST', '/api/_action/sync', [
            'json' => [
                'writeCategory' => [
                    'entity' => 'category',
                    'action' => 'upsert',
                    'payload' => $payload,
                ]
            ]
        ]);

        dd($response);

        return $response;
    }


}
