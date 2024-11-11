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
            $pictures = $productClass->pictures()->get();

            $images = [];

            $shopCategory[] = [
                "id" => $subcategory->sw_id,
                'parentId' => config('app.shopware_default_category'),
                "name" => $subcategory->title,
                "translations" => [
                    "en-GB" => [
                        "name" => $subcategory->title_en,
                    ],
                ]
            ];


            foreach ($pictures as $picture) {
                $images[] = [
                    "URL" => 'http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/pictures/'.$picture->file,
                    "folderName" => $productClass->title
                ];
            }

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
            }

            $export[] = [
                'active' => true,
                'weight' => 0,
                'width' => 0.0,
                'height' => 0.0,
                'length' => 0.0,

                // Allgemeine Informationen
                'id' => $productClass->sw_id,
                'name' => 'Rubrik '.$productClass->rubrik.' '.$productClass->title,
                "productNumber" => $productClass->productnumber,
                'translations' => [
                    'en-GB' => [
                        'name' => $productClass->title_en,
                        'description' => $productClass->description_en,
                    ],
                ],

                // Optionen
                'options' => $options,

                // Bilder (placeholder, adjust based on your logic)
                'Images' => $images,

                //Description
                'description' => $productClass->description,

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
                        'gross' => 11898.81,
                        'net' => 9999,
                        'linked' => false
                    ]
                ],
                'tax' => [
                    'id' => $this->shopWareHelper->getTaxId(),
                ],
                'categories' => $shopCategory,
            ];
        });

        return $export;
    }
}
