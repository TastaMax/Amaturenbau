<?php
namespace App\Http\Controllers\Management\Logs;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogsStorageController extends Controller
{
    private string $log = 'logs/';

    public function getStorageLogEntries(Request $request)
    {
        $logs = $request->logs;

        return response()->json($this->getStorageLog($logs));
    }

    public function getStorageLog($logs)
    {
        $logFilePath = storage_path($this->log.$logs.'.log');
        $entries = [];
        $id = 0;

        if (file_exists($logFilePath)) {
            $lines = array_reverse(file($logFilePath));
            foreach ($lines as $line) {
                preg_match('/^\[(.*?)\] (.*?)\.(.*?): (.*?)$/', $line, $matches);

                if (count($matches) >= 5) {
                    $timestamp = $matches[1];
                    $datetime = new DateTime($timestamp);
                    $logLevel = $matches[3];
                    $logMessage = $matches[4];

                    $color = $this->logStorageLevelColors($logLevel);

                    $entries[] = [
                        'id' => $id,
                        'timestamp' => $datetime->format('H:i:s d.m.Y'),
                        'logLevel' => $logLevel,
                        'level' => '<span class="badge badge-'.$color.'">'.$logLevel.'</span>',
                        'message' => $logMessage
                    ];
                }

                $id++;
            }
        }

        return $entries;
    }


    public function clearStorageLogs(Request $request)
    {
        $logs = $request->logs;
        $logFilePath = storage_path($this->log.$logs.'.log');

        if (File::exists($logFilePath)) {
            File::put($logFilePath, ''); // Leere die Log-Datei
            return redirect()->back()->with('success', 'Log-Einträge wurden gelöscht.');
        } else {
            return redirect()->back()->with('error', 'Log-Datei nicht gefunden.');
        }
    }

    private function logStorageLevelColors($logname): string
    {
        return match ($logname) {
            'ERROR' => 'danger',
            'INFO' => 'primary',
            default => 'secondary',
        };

    }
}
