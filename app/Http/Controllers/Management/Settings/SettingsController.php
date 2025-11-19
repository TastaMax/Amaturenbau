<?php
namespace App\Http\Controllers\Management\Settings;

use App\Http\Controllers\Controller;
use App\ShopWare\Services\ShopwareApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class SettingsController extends Controller
{

    public ShopwareApiClient $shopwareApiClient;

    public function __construct()
    {
        $this->shopwareApiClient = new ShopwareApiClient();
    }

    public function index()
    {
        $shopwareurl = config('app.shopware_base_url');
        $shopwareid = config('app.shopware_client_id');
        $shopwaretoken = config('app.shopware_api_key');

        $shopwaresaleschannelid = config('app.shopware_sales_channel_id');
        $shopwareDefaultCategory = config('app.shopware_default_category');
        $shopwareDefaultManufacturer = config('app.shopware_default_manufacturer');
        $shopwareDefaultTax = config('app.shopware_default_tax');
        $shopwareDefaultCurrency = config('app.shopware_default_currency');

        $shopwareConnection = json_decode($this->shopwareApiClient->checkConnection());
        $shopwareToken =  json_decode($this->shopwareApiClient->checkTokenValidity());
        $salesChannel = null;
        $category = null;
        $taxes = null;
        $manufacturers = null;
        $currencys = null;

        if($shopwareToken && $shopwareConnection)
        {
            $salesChannel = $this->shopwareApiClient->getSalesChannel();
            $category = $this->shopwareApiClient->getCategorys();
            $taxes = $this->shopwareApiClient->getTaxes();
            $manufacturers = $this->shopwareApiClient->getManufacturers();
            $currencys = $this->shopwareApiClient->getCurrencies();
        }

        return view('pages/settings', [
            'url' => $shopwareurl,
            'token' => $shopwaretoken,
            'shopwareId' => $shopwareid,
            'shopwareSalesChannelId' => $shopwaresaleschannelid,
            'shopwareSalesChannels' => $salesChannel,
            'shopwareConnection' => $shopwareConnection,
            'shopwareToken' => $shopwareToken,
            'shopwareCategory' => $category,
            'shopwareDefaultCategory' => $shopwareDefaultCategory,
            'shopwareManufacturers' => $manufacturers,
            'shopwareDefaultManufacturer' => $shopwareDefaultManufacturer,
            'shopwareTaxes' => $taxes,
            'shopwareDefaultTax' => $shopwareDefaultTax,
            'shopwareCurrencys' => $currencys,
            'shopwareDefaultCurrency' => $shopwareDefaultCurrency
        ]);
    }

    public function shopware(Request $request)
    {
        $request->validate([
            'shopwaretoken' => 'required',
            'shopwareurl' => 'required',
            'shopwareid' => 'required',
        ]);

        $shopwareUrl = $request->input('shopwareurl');
        $shopwareid = $request->input('shopwareid');
        $shopwareToken = $request->input('shopwaretoken');
        $shopwareSaleschannelid = $request->input('shopwaresaleschannelid');
        $shopwareDefaultCategory = $request->input('shopwaredefaultcategory');

        $shopwareDefaultTax = $request->input('shopwaredefaulttax');
        $shopwareDefaultManufactor = $request->input('shopwaredefaultmanufacturer');
        $shopwareDefaultCurrency = $request->input('shopwaredefaultcurrency');

        $this->setEnv('SHOPWARE_BASE_URL', $shopwareUrl);
        $this->setEnv('SHOPWARE_CLIENT_ID', $shopwareid);
        $this->setEnv('SHOPWARE_API_KEY', $shopwareToken);
        $this->setEnv('SHOPWARE_SALES_CHANNEL_ID', $shopwareSaleschannelid);
        $this->setEnv('SHOPWARE_DEFAULT_CATEGORY', $shopwareDefaultCategory);

        $this->setEnv('SHOPWARE_DEFAULT_MANUFACTURER', $shopwareDefaultManufactor);
        $this->setEnv('SHOPWARE_DEFAULT_CURRENCY', $shopwareDefaultCurrency);
        $this->setEnv('SHOPWARE_DEFAULT_TAX', $shopwareDefaultTax);

        Session::flash('success', 'Erfolgreich gespeichert');
        return redirect('/einstellungen#shopware');
    }

    public function mail(Request $request)
    {
        Session::flash('success', 'Erfolgreich gespeichert');
        return redirect('/einstellungen#mail');
    }

    private function setEnv($key, $value)
    {
        $envFilePath = base_path('.env');

        // Setze den neuen Wert für die Umgebungsvariable
        putenv("$key=$value");

        // Aktualisiere den Wert in der .env-Datei
        $envContent = File::get($envFilePath);
        $envContent = preg_replace(
            '/^' . $key . '=.*/m',
            $key . '=' . $value,
            $envContent
        );
        File::put($envFilePath, $envContent);
    }
}
