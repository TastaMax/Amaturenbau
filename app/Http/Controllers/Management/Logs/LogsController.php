<?php
namespace App\Http\Controllers\Management\Logs;

use App\Http\Controllers\Controller;
use App\Models\CustomeLog;
use DateTime;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index()
    {
        return view('pages.notification.index', [
            'systems' => $this->getSystems()
        ]);
    }

    public function getSystems()
    {
        $uniqueSystems = CustomeLog::distinct()->pluck('system')->toArray();
        sort($uniqueSystems);

        return $uniqueSystems;
    }

    public function getData(Request $request)
    {
        $system = null;
        if(isset($request->system))
        {
            $system = $request->system;
        }

        $logs = CustomeLog::all();
        $formattedlogs = [];

        foreach ($logs as $log)
        {
            if(!is_null($system))
            {
                if($log['system'] !== $system)
                {
                    continue;
                }
            }
            $created_at = new DateTime($log['created_at']);
            $formattedlogs[] = [
                'id' => $log['id'],
                'system' => $log['system'],
                'message' => $log['message'],
                'importance' => '<span class="p-2 badge badge-'.$this->getImportanceColor($log['importance']).'" title="'.$log['importance'].'">'.$this->getImportanceText($log['importance']).'</span>',
                'created_at' => $created_at->format('Y-m-d H:i:s')
            ];
        }

        return response()->json($formattedlogs);
    }

    public function getLogDetails(Request $request)
    {
        $logId = $request->logId;
        // Find the corresponding model based on the log ID
        $log = CustomeLog::find($logId);

        // Überprüfen, ob das Model gefunden wurde
        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        $date = new DateTime($log->created_at);

        $logDetails = [
            'log' => $log,
            'date' => $date->format('H:i:s d.m.Y'),
            'importanceLevel' => $log['importance'],
            'importanceColor' => $this->getImportanceColor($log->importance),
            'importanceText' => $this->getImportanceText($log->importance)
        ];

        // Model als JSON-Antwort zurückgeben
        return response()->json($logDetails);
    }

    public function getLastLogs($number = 4): array
    {
        $logs = [];
        $lastLogs = CustomeLog::latest()->take($number)->get();
        foreach ($lastLogs as $log) {
            $logs[] = [
                'system' => $log->system,
                'importanceColor' => $this->getImportanceColor($log->importance),
                'message' => $log->message,
                'debug' => json_decode($log->debug),
                'updated_at' => $log->updated_at->format('H:i:s d.m.Y'),
            ];
        }
        return $logs;
    }

    private function getImportanceColor($importance): string
    {
        if ($importance >= 0 && $importance <= 3) {
            return 'info';
        } elseif ($importance >= 4 && $importance <= 7) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    private function getImportanceText($importance): string
    {
        if ($importance >= 0 && $importance <= 3) {
            return 'Normal';
        } elseif ($importance >= 4 && $importance <= 7) {
            return 'Warnung';
        } else {
            return 'Kritisch';
        }
    }
}
