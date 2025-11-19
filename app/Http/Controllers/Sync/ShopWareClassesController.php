<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWProductClass;

class ShopWareClassesController extends Controller
{
    public ShopWareHelperController $shopWareHelper;
    private bool $withoutpictures = false;

    public function __construct()
    {
        $this->shopWareHelper = new ShopWareHelperController();
    }

    public function getClasses($withoutpictures): array
    {
        $export = [];

        $this->withoutpictures = $withoutpictures;

        // Efficiently fetch product classes and their related data using eager loading
        $productClasses = SWProductClass::with(['subCategory.category', 'products.variantValues', 'variantHeaders'])
            ->where('sw_active', 1)
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
                "id" => $category->sw_id,
                "name" => $category->title,
                "translations" => [
                    "en-GB" => [
                        "name" => $category->title_en,
                    ],
                ]
            ];

            if($this->withoutpictures === false) {
                foreach ($pictures as $picture) {
                    $images[] = [
                        "URL" => 'http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/pictures/'.$picture->file,
                        "folderName" => $productClass->title
                    ];
                }
            }


            $allOptions = [];

            foreach ($products as $product) {
                foreach ($product->variantValues as $variantValue) {
                    $variantHeader = $variantHeaders->where('pos', $variantValue->pos)->first();

                    if (!isset($variantHeader)) {
                        // Falls keine Übereinstimmung gefunden wird, debuggen
                        dd($product->variantValues, $variantHeaders);
                    }

                    if ($variantHeader->selectionType != 0) {
                        continue; // Nur mit selectionType == 0 weiterverarbeiten
                    }

                    $option = [
                        'name' => $variantHeader->title,
                        'value' => $this->sanitizeJsonString($variantValue->value),
                        'position' => 0,
                        'name_enGB' => $variantHeader->title_en,
                        'value_enGB' => $this->sanitizeJsonString($variantValue->value_en),
                    ];

                    if (!in_array($option, $allOptions)) {
                        $allOptions[] = $option;
                    }
                }
            }

            // Jetzt hast du alle eindeutigen VariantValues mit selectionType == 0 in $allOptions
            $export[] = [
                'active' => true,
                'ean' =>  '',
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
                'options' => $allOptions,

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

            // Bilder nur hinzufügen, wenn withoutpictures == false
            if ($this->withoutpictures === false) {
                $productExport['Images'] = $images;

                $export[] = $productExport;
            }


        });

        return $export;
    }

    function sanitizeJsonString($string)
    {
        // Entferne nicht druckbare Steuerzeichen (außer Zeilenumbrüche)
        $string = preg_replace('/[\x00-\x1F\x7F]/u', '', $string);

        // Konvertiere falsche Windows-Zeichen in UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }

        // Entferne weitere nicht-druckbare Unicode-Zeichen
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        return $string;
    }

}
