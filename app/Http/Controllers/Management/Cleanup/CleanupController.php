<?php

namespace App\Http\Controllers\Management\Cleanup;

use App\Http\Controllers\Controller;
use App\Models\CustomeLog;
use Carbon\Carbon;

class CleanupController extends Controller
{
    public function cleanLogs()
    {
        // Bestimme das Datum, das 3 Monate in der Vergangenheit liegt
        $threeMonthsAgo = Carbon::now()->subMonths(1);

        // Lösche Datensätze, die älter als 3 Monate sind
        CustomeLog::where('created_at', '<', $threeMonthsAgo)->delete();

        CustomeLog::create([
            'system' => 'Cleanup',
            'importance' => 0,
            'message' => 'Bereinigung der Log Dateien abgeschlossen',
            'debug' => json_encode([])
        ]);

        return true;
    }
}
