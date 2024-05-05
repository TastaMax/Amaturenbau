<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;

class NIFIController extends Controller
{

    public function getJsonProducts()
    {
        $swProducts = new ShopWareProductsController();
        return $swProducts->getProducts();
    }

    public function getJsonClasses()
    {
        $swClasses = new ShopWareClassesController();
        return $swClasses->getClasses();
    }
}
