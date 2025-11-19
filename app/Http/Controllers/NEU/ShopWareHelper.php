<?php
namespace App\Http\Controllers\NEU;

use App\Http\Controllers\Controller;

class ShopWareHelper extends Controller
{
    public function generateUUID($length = 32): string
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

    public function getDefaultCategory(): string
    {
        return config('app.shopware_default_category');
    }
}
