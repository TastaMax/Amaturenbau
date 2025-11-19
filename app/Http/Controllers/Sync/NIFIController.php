<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NIFIController extends Controller
{

    public function getJsonProducts()
    {
        $swProducts = new ShopWareProductsController();
        return $swProducts->getProducts();
    }

    public function getJsonClasses(Request $request)
    {
        $withoutpictures = $request->query('withoutpictures', false); // Standardwert: false
        $swClasses = new ShopWareClassesController();
        $json = json_encode($swClasses->getClasses($withoutpictures), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return response($json)->header('Content-Type', 'application/json; charset=UTF-8');
    }
}
