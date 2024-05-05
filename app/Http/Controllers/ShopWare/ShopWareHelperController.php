<?php
namespace App\Http\Controllers\ShopWare;

use App\Http\Controllers\Controller;

class ShopWareHelperController extends Controller
{
    public function generateUUID($length): string
    {
        $characters = '123456789abcdef';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function getCurrencyId(): string
    {
        return config('app.shopware_default_currency');
    }

    public function getTaxId(): string
    {
        return config('app.shopware_default_tax');
    }

    public function getManufacturerId(): string
    {
        return config('app.shopware_default_manufacturer');
    }
}
