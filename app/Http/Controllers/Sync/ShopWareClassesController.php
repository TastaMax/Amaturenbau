<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWProductClass;

class ShopWareClassesController extends Controller
{
    public ShopWareHelperController $shopWareHelper;

    public function __construct()
    {
        $this->shopWareHelper = new ShopWareHelperController();
    }

    public function getClasses(): array
    {
        $export = [];

        // Efficiently fetch product classes and their related data using eager loading
        $productClasses = SWProductClass::with(['subCategory.category', 'products.variantValues', 'variantHeaders'])
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
                        //Wenn Positionen der Values nicht mit den Headers matcht großes Problem!
                        dd($variantValues, $variantHeaders);
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
                    'parentId' => $productClass->sw_id,
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
                        'id' => $this->shopWareHelper->getManufacturerId(),
                    ],
                    'maxPurchase' => 0,
                    'minPurchase' => 1,
                    'purchaseUnit' => 1,
                    'restockTime' => 0,
                    'stock' => 99,
                    'price' => [
                        [
                            'currencyId' => $this->shopWareHelper->getCurrencyId(),
                            'gross' => $product->price,
                            'net' => $product->price * 0.81,
                            'linked' => false
                        ]
                    ],
                    'tax' => [
                        'id' => $this->shopWareHelper->getTaxId(),
                    ],
                ];
            }
        });

        return $export;
    }
}
