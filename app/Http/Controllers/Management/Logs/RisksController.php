<?php
namespace App\Http\Controllers\Management\Logs;

use App\Http\Controllers\Controller;
use App\Models\CustomeLog;
use Carbon\Carbon;

class RisksController extends Controller
{
    public int $riskCount = 0;
    public array $risks = [];

    public function getRisks()
    {
        $this->getLogRisks('laravel', 'Laravel System');
        $this->getLogRisks('import', 'Import System');
        $this->getNotificationRisks();

        $code = $this->getCode($this->risks);

        return json_encode([
            'risks' => $this->risks,
            'riskCount' => $this->riskCount,
            'colorCode' => $code[1],
            'messageCode' => $code[0],
        ]);
    }

    private function getNotificationRisks()
    {
        $lastWeek = Carbon::now()->subWeek();
        $logs = CustomeLog::whereBetween('created_at', [$lastWeek, Carbon::now()])->get();

        $notifications = [];

        foreach ($logs as $log) {
            $system = $log->system;
            $importance = $log->importance;

            if ($importance >= 4 && $importance <= 7) {
                if (isset($notifications[$system])) {
                    $existingEntry = $notifications[$system];
                    if ($importance < 7) {
                        $existingEntry['type'] = 'danger';
                        $existingEntry['importance'] = $importance;
                        continue;
                    }
                } else {
                    $newEntry = [
                        'type' => 'warning',
                        'system' => $system,
                        'importance' => $importance,
                    ];
                    $notifications[$system] = $newEntry;
                }
            }
            if ($importance > 7) {
                $newEntry = [
                    'type' => 'danger',
                    'system' => $system,
                    'importance' => $importance,
                ];
                $notifications[$system] = $newEntry;
            }
        }

        foreach ($notifications as $notification)
        {
            $this->risks[] = [
                'name' => $notification['system'],
                'importance' => $this->getImportanceText($notification['importance']),
                'badge' => $notification['type'],
            ];
        }
    }

    private function getLogRisks($logname, $systemname)
    {
        $count = 0;
        $riskcount = 0;
        $importance = 'Normal';
        $badge = 'primary';

        $logsController = new LogsStorageController();
        $logs = $logsController->getStorageLog($logname);
        if(count($logs) > 0)
        {
            foreach ($logs as $log)
            {
                if ($log['logLevel'] == 'CRITICAL' || $log['logLevel'] == 'ERROR')
                {
                    $count++;
                    $riskcount += 10;
                    $importance = 'Kritisch';
                    $badge = 'danger';
                }else if($log['logLevel'] == 'WARNING')
                {
                    $count++;
                    $riskcount += 5;

                    if($importance != 'critical')
                    {
                        $importance = 'Warnung';
                        $badge = 'warning';
                    }

                }else{
                    $count++;
                    $riskcount += 1;
                }
            }

            $this->riskCount += $riskcount/$count;
        }

        if($importance != 'Normal')
        {
            $this->risks[] = [
                'name' => $systemname,
                'importance' => $importance,
                'badge' => $badge,
            ];
        }

        return true;
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

    private function getCode($risks): array
    {
        if(count($risks) > 0)
        {
            foreach ($risks as $risk)
            {
                if($risk['importance'] == 'Kritisch')
                {
                    return ['<i class="fa-solid fa-xmark fa-2x"></i>', '#e74c3c'];
                }
            }
            return ['<i class="fa-solid fa-triangle-exclamation fa-2x"></i>', '#f1c40f'];
        }
        return ['<i class="fa-solid fa-check fa-2x"></i>', '#2ecc71'];
    }
}
