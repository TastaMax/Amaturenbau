<?php

namespace App\ShopWare\Agent;

use App\Http\Controllers\Controller;
use App\Models\SWCategory;
use App\Models\SWSubCategory;
use App\ShopWare\Services\ShopwareApiService;
use App\ShopWare\Services\ShopwareApiHelper;
use Illuminate\Support\Facades\Log;

class CategorySyncController extends Controller
{
    protected ShopwareApiService $shopwareApiService;
    protected ShopwareApiHelper $shopwareApiHelper;
    protected mixed $defaultParentId;

    public function __construct(ShopwareApiService $shopwareApiService, ShopwareApiHelper $shopwareApiHelper)
    {
        $this->shopwareApiService = $shopwareApiService;
        $this->shopwareApiHelper = $shopwareApiHelper;
        $this->defaultParentId = config('app.shopware_default_category');
    }

    public function syncCategories()
    {
        try {
            // Get all local categories with their subcategories
            $localCategories = SWCategory::with('subcategories')->get();

            if ($localCategories->isEmpty()) {
                return ['success' => true, 'message' => 'No categories to sync.'];
            }

            $results = [
                'categories' => [
                    'created' => 0,
                    'updated' => 0,
                ],
                'subcategories' => [
                    'created' => 0,
                    'updated' => 0,
                ],
                'errors' => [],
                'details' => []
            ];

            foreach ($localCategories as $category) {
                try {
                    $categoryData = $this->prepareCategoryData($category);

                    // Try to find existing category by name first
                    $existingCategory = $this->findCategoryByName($category->title);

                    if ($existingCategory) {
                        // Update existing category
                        $this->shopwareApiService->updateCategory($existingCategory['id'], $categoryData);
                        $results['categories']['updated']++;
                        $results['details'][] = "Updated category: {$category->title} (ID: {$existingCategory['id']})";
                        $shopwareCategoryId = $existingCategory['id'];
                    } else {
                        // Create new category
                        $response = $this->shopwareApiService->createCategory($categoryData);
                        $shopwareCategoryId = $response['data']['data']['id'];
                        $results['categories']['created']++;
                        $results['details'][] = "Created category: {$category->title} (ID: {$shopwareCategoryId})";
                    }

                    // Sync subcategories for this category
                    if ($category->subcategories && $category->subcategories->isNotEmpty()) {
                        $subResults = $this->syncSubCategories($category->subcategories, $shopwareCategoryId);
                        $results['subcategories']['created'] += $subResults['created'];
                        $results['subcategories']['updated'] += $subResults['updated'];
                        $results['details'] = array_merge($results['details'], $subResults['details']);
                        $results['errors'] = array_merge($results['errors'], $subResults['errors']);
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Failed to sync category '{$category->title}' (ID: {$category->id}): " . $e->getMessage();
                    Log::error($errorMsg);
                    $results['errors'][] = $errorMsg;
                }
            }

            $results['success'] = true;
            $results['message'] = sprintf(
                'Sync completed. Categories - Created: %d, Updated: %d | SubCategories - Created: %d, Updated: %d | Errors: %d',
                $results['categories']['created'],
                $results['categories']['updated'],
                $results['subcategories']['created'],
                $results['subcategories']['updated'],
                count($results['errors'])
            );

            return $results;

        } catch (\Exception $e) {
            $errorMsg = 'Category sync failed: ' . $e->getMessage();
            Log::error($errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
                'errors' => [$errorMsg]
            ];
        }
    }

    protected function syncSubCategories($subcategories, string $parentCategoryId): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => [],
            'details' => []
        ];

        foreach ($subcategories as $subcategory) {
            try {
                $subcategoryData = $this->prepareSubCategoryData($subcategory, $parentCategoryId);

                // Try to find existing subcategory by name
                $existingSubCategory = $this->findCategoryByName($subcategory->title);

                if ($existingSubCategory) {
                    // Update existing subcategory
                    $this->shopwareApiService->updateCategory($existingSubCategory['id'], $subcategoryData);
                    $results['updated']++;
                    $results['details'][] = "  └─ Updated subcategory: {$subcategory->title} (ID: {$existingSubCategory['id']})";
                } else {
                    // Create new subcategory
                    $response = $this->shopwareApiService->createCategory($subcategoryData);
                    $results['created']++;
                    $results['details'][] = "  └─ Created subcategory: {$subcategory->title} (ID: {$response['data']['data']['id']})";
                }

            } catch (\Exception $e) {
                $errorMsg = "Failed to sync subcategory '{$subcategory->title}' (ID: {$subcategory->id}): " . $e->getMessage();
                Log::error($errorMsg);
                $results['errors'][] = $errorMsg;
            }
        }

        return $results;
    }

    protected function findCategoryByName(string $name): ?array
    {
        try {
            $response = $this->shopwareApiService->searchCategories([
                'filter' => [
                    ['field' => 'name', 'type' => 'equals', 'value' => $name]
                ],
                'limit' => 1
            ]);

            return $response['data']['data'][0] ?? null;
        } catch (\Exception $e) {
            Log::warning("Failed to find category by name '{$name}': " . $e->getMessage());
            return null;
        }
    }

    protected function prepareCategoryData(SWCategory $category): array
    {
        return [
            'id' => $this->shopwareApiHelper->generateId($category->title),
            'parentId' => $this->defaultParentId,
            'active' => true,
            'visible' => true,
            'type' => 'page',
            'name' => $category->title,
            'metaTitle' => $category->meta_title ?? $category->title,
            'metaDescription' => $category->meta_description ?? '',
            'keywords' => $category->meta_keywords ?? '',
            'translations' => [
                'de-DE' => [
                    'name' => $category->title,
                    'metaTitle' => $category->meta_title ?? $category->title,
                    'metaDescription' => $category->meta_description ?? '',
                    'keywords' => $category->meta_keywords ?? '',
                ],
                'en-GB' => [
                    'name' => $category->title_en ?? $category->title,
                    'metaTitle' => $category->meta_title ?? $category->title_en ?? $category->title,
                    'metaDescription' => $category->meta_description ?? '',
                    'keywords' => $category->meta_keywords ?? '',
                ]
            ]
        ];
    }

    protected function prepareSubCategoryData(SWSubCategory $subcategory, string $parentCategoryId): array
    {
        return [
            'id' => $this->shopwareApiHelper->generateId($subcategory->title),
            'parentId' => $parentCategoryId,
            'active' => true,
            'visible' => true,
            'type' => 'page',
            'name' => $subcategory->title,
            'metaTitle' => $subcategory->meta_title ?? $subcategory->title,
            'metaDescription' => $subcategory->meta_description ?? '',
            'keywords' => $subcategory->meta_keywords ?? '',
            'translations' => [
                'de-DE' => [
                    'name' => $subcategory->title,
                    'metaTitle' => $subcategory->meta_title ?? $subcategory->title,
                    'metaDescription' => $subcategory->meta_description ?? '',
                    'keywords' => $subcategory->meta_keywords ?? '',
                ],
                'en-GB' => [
                    'name' => $subcategory->title_en ?? $subcategory->title,
                    'metaTitle' => $subcategory->meta_title ?? $subcategory->title_en ?? $subcategory->title,
                    'metaDescription' => $subcategory->meta_description ?? '',
                    'keywords' => $subcategory->meta_keywords ?? '',
                ]
            ]
        ];
    }
}
