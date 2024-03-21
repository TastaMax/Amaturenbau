<?php

namespace App\Console;

use App\Http\Controllers\Management\Logs\SchedulesController;
use App\Http\Controllers\Sync\SyncController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    /**
     * WICHTIG: Nicht Sonntag 20Uhr, geplanter Neustart von Debian!!!
     **/
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            (new SyncController)->category();
            (new SchedulesController)->updateSchedules('ShopWare6 Sync', 'Kategorie Synchronisieren');
        })->everySecond();//->everyTenMinutes()->weekdays()->between('7:00', '18:00');

        $schedule->call(function () {
            (new SyncController)->subcategory();
            (new SchedulesController)->updateSchedules('ShopWare6 Sync', 'Unterkategorie Synchronisieren');
        })->everySecond();//->everyTenMinutes()->weekdays()->between('7:00', '18:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
