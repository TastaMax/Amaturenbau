<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\SWProductClass;
use App\Services\ShopwareApiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NIFIController extends Controller
{
    public ShopwareApiClient $shopwareApiClient;

    public function __construct(ShopwareApiClient $shopwareApiClient)
    {
        $this->shopwareApiClient = $shopwareApiClient;
    }

    public function index(): array
    {
        $export = [];

        // Efficiently fetch product classes and their related data using eager loading
        $productClasses = SWProductClass::where('sw_edited', true)
            ->with(['subCategory.category', 'products.variantValues', 'variantHeaders'])
            ->get();

        if ($productClasses->isEmpty()) {
            return $export;
        }

        // Process product classes in batches for memory efficiency
        $productClasses->each(function (SWProductClass $productClass) use (&$export) {
            $subcategory = $productClass->subCategory;
            $category = $subcategory->category;
            $products = $productClass->products;
            $variantHeaders = $productClass->variantHeaders;

            foreach ($products as $product) {
                $variantValues = $product->variantValues;

                $options = [];
                $pos = 10;
                foreach ($variantValues as $variantValue) {
                    $variantHeader = $variantHeaders->where('pos', $variantValue->pos)->first();

                    if(!isset($variantHeader))
                    {
                        dd($product, $variantValue->pos, $variantHeaders, $variantValues);
                    }

                    $options[] = [
                        'name' => $variantHeader->title,
                        'value' => $variantValue->value,
                        'position' => $pos,
                        'name_enGB' => $variantHeader->title_en,
                        'value_enGB' => $variantValue->value_en,
                    ];
                    $pos += 10;
                }

                $export[] = [
                    'active' => true,
                    'weight' => $product->weight,
                    'width' => 0.0,
                    'height' => 0.0,
                    'length' => 0.0,

                    // Allgemeine Informationen
                    'id' => $product->sw_id,
                    'name' => $category->title,
                    "productNumber" => $product->articlenumber,
                    'translations' => [
                        'en-GB' => [
                            'name' => $category->title_en
                        ]
                    ],

                    // Optionen
                    'options' => $options,

                    // Bilder (placeholder, adjust based on your logic)
                    'Images' => [
                        [
                            "URL" => "https://data.shop.ass-automation.com/pictures/1-000-05-00_I.png",
                            "folderName" => "SWM"
                        ]
                    ],

                    // Preis Informationen
                    'markAsTopseller' => false,
                    'manufacturer' => [
                        'id' => config('app.shopware_default_manufacturer'),
                    ],
                    'maxPurchase' => 0,
                    'minPurchase' => 1,
                    'purchaseUnit' => 1,
                    'restockTime' => 0,
                    'stock' => 99,
                    'price' => [
                        [
                            'currencyId' => config('app.shopware_default_currency'),
                            'gross' => $product->price,
                            'net' => 9999.0,
                            'linked' => false
                        ]
                    ],
                    'tax' => [
                        'id' => config('app.shopware_default_tax')
                    ],
                ];
            }
        });

        return $export;
    }
}
