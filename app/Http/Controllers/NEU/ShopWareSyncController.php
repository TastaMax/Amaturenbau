<?php

namespace App\Http\Controllers\NEU;

use App\Models\SWCategory;
use App\Models\SWVariantHeader;
use Illuminate\Support\Facades\Log;

class ShopWareSyncController
{
    protected ShopWareSyncPropertyOption $swClient;

    public function __construct()
    {
        $this->swClient = new ShopWareSyncPropertyOption();
    }

    public function index()
    {
        //Step 1 Sync Properties and Options
        //$this->syncPropertiesAndOptions();

        //Step 2 Sync Categories
        $this->syncCategories();
    }

    public function syncCategories()
    {
        $swSyncCategory = new ShopWareSyncCategory();

        $categories = SWCategory::with('subcategories')->get();

        $categoryData = [];

        foreach ($categories as $category) {
            $subCategories = $category->subcategories;

            $subData = $subCategories->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'sw_id' => $sub->sw_id,
                    'title' => $sub->title,
                    'title_en' => $sub->title_en,
                    'meta_title' => $sub->meta_title,
                    'meta_description' => $sub->meta_description,
                    'meta_keywords' => $sub->meta_keywords,
                    'active' => $sub->sw_active,
                ];
            })->toArray();

            $categoryData[] = [
                'id' => $category->id,
                'sw_id' => $category->sw_id,
                'title' => $category->title,
                'title_en' => $category->title_en,
                'meta_title' => $category->meta_title,
                'meta_description' => $category->meta_description,
                'meta_keywords' => $category->meta_keywords,
                'active' => $category->sw_active,
                'subcategories' => $subData,
            ];
        }

        $response = $swSyncCategory->bulkCreateCategories($categoryData, false);

        return response()->json([
            'status' => 'Kategoriesynchronisation abgeschlossen',
            'success' => $response['success'] ?? false,
            'data' => $response['data'] ?? [],
        ]);
    }

    public function syncPropertiesAndOptions()
    {
        $swSyncPropertyOption = new ShopWareSyncPropertyOption();

        $headers = SWVariantHeader::with(['swProductClass.products.variantValues'])->get();

        $propertyData = [];

        foreach ($headers as $header) {
            // Property Name (z.B. "Farbe")
            $propertyName = $header->title;  // Standard: Deutsch

            // Wenn ein englischer Titel existiert, nutze diesen
            $propertyNameEn = $header->title_en ?? $propertyName;

            // Werte aus den Produkten und deren Varianten
            $values = $header->swProductClass
                ->products
                ->flatMap(fn($product) => $product->variantValues)
                ->map(function($variantValue) {
                    // Extrahiert sowohl den deutschen als auch den englischen Wert
                    return [
                        'de' => $variantValue->value,  // Wert in Deutsch
                        'en' => $variantValue->value_en,  // Wert in Englisch
                    ];
                })
                ->filter(fn($value) => !empty($value['de']) || !empty($value['en']))  // Filtert leere Werte
                ->unique(fn($value) => $value['de'] . '|' . $value['en'])  // Entfernt doppelte Kombinationen von Deutsch und Englisch
                ->values()
                ->toArray();

            // Property und deren Werte in ein assoziatives Array einfügen
            $propertyData[$propertyName] = [
                'de' => $values,  // Die Werte auf Deutsch
                'en' => $values,  // Die gleichen Werte auf Englisch
                'title' => $propertyName,  // Deutscher Titel
                'title_en' => $propertyNameEn,  // Englischer Titel
            ];
        }

        // Synchronisation an ShopWare
        $response = $swSyncPropertyOption->bulkCreatePropertyGroupWithValues($propertyData, false);

        return response()->json([
            'status' => 'Synchronisation abgeschlossen',
            'success' => $response['success'],
            'data' => $response['data']
        ]);
    }

}
