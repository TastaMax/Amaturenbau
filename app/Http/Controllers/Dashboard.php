<?php

namespace App\Http\Controllers;

class Dashboard extends Controller
{
    public function index()
    {
        return view('pages/dashboard', [
            'informations' => $this->getInformations(),
        ]);
    }

    private function getInformations(): array
    {
        // PHP-Version abrufen
        $phpVersion = phpversion();

        // Laravel-Version abrufen
        $laravelVersion = app()->version();

        //OS abrufen
        $operatingSystemName = php_uname('s');

        $operatingSystemVersion = php_uname('v');
        // String, den du entfernen möchtest
        $detailsToRemove = '#1 SMP PREEMPT_DYNAMIC';

        // Entferne den String aus dem Betriebssysteminfo-String
        $operatingSystemVersion = str_replace($detailsToRemove, '', $operatingSystemVersion);

        return [
            'phpversion' => $phpVersion,
            'laravelversion' => $laravelVersion,
            'osversion' => $operatingSystemName.' '.$operatingSystemVersion,
            'lastupdate' => config('app.app_update')
        ];
    }

}
