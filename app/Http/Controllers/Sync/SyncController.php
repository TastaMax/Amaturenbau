<?php
namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWCategory;
use App\Models\SWProductClass;
use App\Models\SWSubCategory;
use App\Models\SWVariantCache;
use App\Services\ShopwareApiClient;

class SyncController extends Controller
{
    public ShopwareApiClient $shopwareApiClient;
    public ShopWareHelperController $shopWareHelper;

    public function __construct()
    {
        $this->shopwareApiClient = new ShopwareApiClient();
        $this->shopWareHelper = new ShopWareHelperController();
    }

    public function index()
    {
        return view('pages/sync/index', []);
    }

    public function category()
    {
        $defaultCategory = config('app.shopware_default_category');
        $payloadCategory = [];

        /*
         * Insert/Update Category
         */
        $categorys = SWCategory::where('sw_edited', true)->get();

        if(count($categorys) > 0) {
            foreach ($categorys as $category) {
                $payloadCategory[] = [
                    'id' => $category->sw_id,
                    'active' => true,
                    'name' => $category->title,
                    'parentId' => $defaultCategory,
                    'metaTitle' => $category->meta_title,
                    'metaDescription' => $category->meta_description,
                    'keywords' => $category->meta_keywords,
                    'translations' => [
                        'en-GB' => [
                            'name' => $category->title_en,
                        ]
                    ]
                ];
            }
            $this->shopwareApiClient->sync('category', 'category', 'upsert', $payloadCategory);
            SWCategory::where('sw_edited', true)->update(['sw_edited' => false]);
        }

        /*
         * Delete Category
         */
        $categorys = SWCategory::where('sw_deleted', true)->get();

        if(count($categorys) > 0) {
            foreach ($categorys as $category) {
                $payloadCategory[] = [
                    'id' => $category->sw_id,
                ];
            }
            $this->shopwareApiClient->sync('category', 'category', 'delete', $payloadCategory);
            SWCategory::where('sw_deleted', true)->delete();
        }
        return true;
    }

    public function subcategory()
    {
        $payloadSubCategory = [];
        $subCategorys = SWSubCategory::where('sw_edited', true)->get();

        if(count($subCategorys) == 0)
        {
            return true;
        }

        foreach ($subCategorys as $subCategory)
        {
            $category = $subCategory->category;
            $payloadSubCategory[] = [
                'id' => $subCategory->sw_id,
                'active' => true,
                'name' => $subCategory->title,
                'parentId' => $category->sw_id,
                'metaTitle' => $subCategory->meta_title,
                'metaDescription' => $subCategory->meta_description,
                'keywords' => $subCategory->meta_keywords,
                'translations' => [
                    'en-GB' => [
                        'name' => $subCategory->title_en,
                    ]
                ]
            ];
        }
        $this->shopwareApiClient->sync('subcategory', 'category', 'upsert', $payloadSubCategory);
        SWSubCategory::where('sw_edited', true)->update(['sw_edited' => false]);

        return true;
    }

    public function product()
    {
        $payloadProducts = [];
        $payloadConfigurationSettings = [];

        $productclasses = SWProductClass::where('sw_edited', true)->limit(3)->get();

        if(!$productclasses) {
            return true;
        }

        foreach ($productclasses as $productclass)
        {
            $category = $productclass->subCategory()->first(); //Kategorie abholen
            $products = $productclass->products()->get(); //Alle Produkte der Produktklasse
            $variantHeaders = $productclass->variantHeaders()->get(); //Alle Headers

            foreach ($products as $product)
            {
                $variants = [];
                $variantValues = $product->variantValues()->get(); //Alle Values

                foreach ($variantValues as $variantValue)
                {
                    $variantHeaderForPos = $variantHeaders->where('pos', $variantValue->pos)->first();
                    $idVariant = $this->searchVariants($variantHeaderForPos->title, $variantValue->value);
                    if(is_null($idVariant))
                    {
                        $swVariantHeader = $this->shopwareApiClient->searchPropertyGroupIdByName($variantHeaderForPos->title);
                        if(!$swVariantHeader)
                        {
                            dd($variantValue);
                        }

                        if(!isset($swVariantHeader['body']['data'][0]))
                        {
                            //VariantHeader zu SW6
                            $idVariantHeader = $this->shopWareHelper->generateUUID(32);
                            $this->shopwareApiClient->createPropertyGroupIdByName($variantHeaderForPos->title, $variantHeaderForPos->title_en, $idVariantHeader);
                        }else{
                            //VariantHeader gefunden
                            $idVariantHeader = $swVariantHeader['body']['data'][0]['id'];
                        }

                        $swVariantValue = $this->shopwareApiClient->searchPropertyGroupOptions($idVariantHeader, $variantValue->value);
                        if(!isset($swVariantValue['body']['data'][0]))
                        {
                            $idVariantValue = $this->shopWareHelper->generateUUID(32);
                            $this->shopwareApiClient->createPropertyGroupOption($idVariantHeader, $variantValue->value, $variantValue->value_en, $idVariantValue);
                        }else{
                            $idVariantValue = $swVariantValue['body']['data'][0]['id'];
                        }

                        $search = [
                            'header' => $variantHeaderForPos->title,
                            'value' => $variantValue->value
                        ];

                        $data = [
                            'header' => $variantHeaderForPos->title,
                            'value' => $variantValue->value,
                            'sw_id' => $idVariantValue,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $variants[] = [
                            'optionId' => $idVariantValue
                        ];
                        SWVariantCache::updateOrInsert($search, $data);
                    }else{
                        $variants[] = [
                            'optionId' => $idVariant->sw_id,
                            'id' => $this->shopWareHelper->generateUUID(32)
                        ];
                    }
                }

                $payloadConfigurationSettings[] = [
                    'id' => $product->sw_id,

                ];

                $payloadProducts[] = [
                    'id' => $product->sw_id,
                    'active' => true,
                    'ean' => '',
                    'weight' => $product->weight,
                    'width' => 0.0,
                    'height' => 0.0,
                    'length' => 0.0,
                    'keywords' => '',
                    'description' => $productclass->description,
                    'name' => $productclass->title,
                    'markAsTopseller' => false,
                    'minPurchase' => 1,
                    'purchaseUnit' => 1,
                    'restockTime' => 0,
                    'stock' => 99,
                    'productNumber' => $product->articlenumber,
                    'configuratorSettings' => $variants,
                    'tax' => [
                        'id' => config('app.shopware_default_tax')
                    ],
                    'manufacturer' => [
                      'id' => config('app.shopware_default_manufacturer'),
                    ],
                    'translations' => [
                        'en-GB' => [
                            'description' => $productclass->description_en,
                            'name' => $productclass->title_en
                        ]
                    ],
                    'categories' => [
                        [
                            'id' => $category->sw_id
                        ]
                    ],
                    'price' => [
                        [
                            'currencyId' => config('app.shopware_default_currency'),
                            'gross' => $product->price,
                            'net' => 9999.0,
                            'linked' => false
                        ]
                    ],
                    'visibilities' => [
                        [
                            'id' => $this->shopWareHelper->generateUUID(32),
                            'salesChannelId' => config('app.shopware_sales_channel_id'),
                            'visibility' => 30
                        ]
                    ]
                ];
                dd($payloadProducts);
            }
        }

       dd($this->shopwareApiClient->sync('product', 'product', 'upsert', $payloadProducts));

        return true;
    }

    private function searchVariants($header, $value)
    {
        $variant = SWVariantCache::where('header', $header)
            ->where('value', $value)
            ->first();
        if ($variant) {
            return $variant;
        } else {
            return null;
        }
    }
}
