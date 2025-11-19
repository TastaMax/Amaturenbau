<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\SWCategory;
use App\ShopWare\Services\ShopwareApiClient;

class ShopWareMainCategoryController extends Controller
{
    public function syncMainCategory()
    {
        $shopwareApiClient = new ShopwareApiClient();

        $mainCategory = config('app.shopware_default_category'); //Haupt Kategorie holen

        $activeCategories = SWCategory::where('sw_active', false)->get();

        foreach ($activeCategories as $activeCategory) {
            $category = [
                'id' => $activeCategory->sw_id,
                'name' => $activeCategory->title,
                'name_en' => $activeCategory->title_en,
                'parentId' => $mainCategory,
                'active' => true,
            ];
            $shopwareApiClient->postCategory($category);
        }

        dd($mainCategory);
    }
}
