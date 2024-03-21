<?php
namespace App\Http\Controllers\Startup;

use App\Http\Controllers\Controller;
use App\Models\CustomeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\ShopwareApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;

class StartupController extends Controller
{

    private ShopwareApiClient $shopwareApiClient;

    public function index()
    {

        if(config('app.app_startup')){
            return redirect()->route('login');
        }

        $this->shopwareApiClient = new ShopwareApiClient();
        $company = config('app.app_company');
        $shopwareurl = config('app.shopware_base_url');
        $shopwareid = config('app.shopware_client_id');
        $shopwaretoken = config('app.shopware_api_key');
        $shopwaresaleschannelid = config('app.shopware_sales_channel_id');

        $state = null;
        $shopwareConnection = null;
        $shopwareToken = null;
        $salesChannel = null;
        $dbConnection = null;


        if($company !== "")
        {
            try {
                DB::connection()->getPdo();
                $dbConnection = DB::connection()->getDatabaseName();
                if($dbConnection == '')
                {
                    $dbConnection = 'Es fehlen wichtige Verbindungsinformationen wie z.B. Host,Port,Name usw. bitte Prüfen.';
                }else
                {
                    $dbConnection = true;
                }

            } catch (\Exception $e) {
                $dbConnection = $e->getMessage();
            }

        }else{
            $dbConnection = true;
        }

        if($shopwareurl !== '' || $shopwareid !== '' || $shopwaretoken !== '')
        {
            $shopwareConnection = $this->shopwareApiClient->checkConnection();
            $shopwareToken = $this->shopwareApiClient->checkTokenValidity();
        }

        if($company == '' || $company == '""')
        {
            $state = 0;
        }
        elseif($dbConnection !== true)
        {
            $state = 1;
        }
        elseif($shopwareurl == '' || $shopwareid == '' || $shopwaretoken == '' || $shopwaresaleschannelid == '')
        {
            $state = 2;

            if($shopwareToken && $shopwareConnection)
            {
                $salesChannel = $this->shopwareApiClient->getSalesChannel();
            }
        }else{
            if(!$shopwareToken || !$shopwareConnection)
            {
                $state = 2;
            }else
            {
                $state = 3;
            }
        }

        return view('pages/startup', [
            'state' => $state,
            'dbError' => $dbConnection,
            'shopwareConnection' => $shopwareConnection,
            'shopwareToken' => $shopwareToken,
            'shopwareSalesChannels' => $salesChannel,
            'phpSettings' => [
                'maxFileSize' => $this->parseSize(ini_get('upload_max_filesize')),
                'memoryLimit' => $this->parseSize(ini_get('memory_limit')),
                'timeout' => ini_get('max_execution_time'),
                'mssql' => extension_loaded('sqlsrv')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'submit' => 'required|integer',
        ]);

        $state = $request->input('submit');

        if($state == 0)
        {
            $request->validate([
                'companyName' => 'required|string',
                'appUrl' => 'required|string',
            ]);

            $companyName = $request->input('companyName');
            $appUrl = $request->input('appUrl');

            $this->setEnv('APP_COMPANY', $companyName);
            $this->setEnv('APP_URL', $appUrl);

            Session::flash('success', 'Erfolgreich gespeichert');
            return redirect('/startup');
        }
        if($state == 1)
        {
            $request->validate([
                'host' => 'required|string',
                'port' => 'required|string',
                'name' => 'required|string',
                'username' => 'required|string',
            ]);

            $host = $request->input('host');
            $port = $request->input('port');
            $name = $request->input('name');
            $username = $request->input('username');
            $password = $request->input('password');

            $this->setEnv('DB_HOST', $host);
            $this->setEnv('DB_PORT', $port);
            $this->setEnv('DB_DATABASE', $name);
            $this->setEnv('DB_USERNAME', $username);
            $this->setEnv('DB_PASSWORD', $password);

            Session::flash('success', 'Erfolgreich gespeichert');
            return redirect('/startup');
        }
        if($state == 2)
        {
            $request->validate([
                'shopwaretoken' => 'required',
                'shopwareurl' => 'required',
                'shopwareid' => 'required',
            ]);

            $shopwareUrl = $request->input('shopwareurl');
            $shopwareid = $request->input('shopwareid');
            $shopwareToken = $request->input('shopwaretoken');

            if($request->input('shopwaresaleschannelid') !== null)
            {
                $shopwareSaleschannelid = $request->input('shopwaresaleschannelid');
                $this->setEnv('SHOPWARE_SALES_CHANNEL_ID', $shopwareSaleschannelid);
            }

            $this->setEnv('SHOPWARE_BASE_URL', $shopwareUrl);
            $this->setEnv('SHOPWARE_CLIENT_ID', $shopwareid);
            $this->setEnv('SHOPWARE_API_KEY', $shopwareToken);

            Session::flash('success', 'Erfolgreich gespeichert');
            return redirect('/startup');
        }else{
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);

            $email = $request->input('email');
            $password = $request->input('password');

            try {
                Artisan::call('migrate:fresh');

                $user = new User();
                $user->name = 'Admin';
                $user->email = $email;
                $user->password = bcrypt($password);
                $user->save();

                $log = new CustomeLog();
                $log->importance = 0;
                $log->system = 'Installation';
                $log->message = 'Die Installation ist erfolgreich abgeschlossen.';
                $log->save();

                $this->setEnv('APP_STARTUP', "true");
                Session::flash('success', 'Erfolgreich Installiert!');
            } catch (\Exception $e) {
                Session::flash('error',  $e->getMessage());
                return redirect('/startup');
            }
        }

        return redirect('/');
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Entferne alle nicht alphanumerischen Zeichen, um die Einheit zu erhalten
        $size = preg_replace('/[^0-9\.]/', '', $size); // Entferne alle nicht numerischen Zeichen, um die Zahl zu erhalten

        return match (strtolower($unit)) {
            'k' => (int)$size * 1024,
            'm' => (int)$size * 1024 * 1024,
            'g' => (int)$size * 1024 * 1024 * 1024,
            default => (int)$size,
        };
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
            $key . '=' . '"'.$value.'"',
            $envContent
        );
        File::put($envFilePath, $envContent);
    }
}
