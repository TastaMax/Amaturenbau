<?php
namespace App\Http\Controllers\Management\Logs;

use App\Http\Controllers\Controller;
use App\Models\ScheduleService;
use Carbon\Carbon;

class SchedulesController extends Controller
{

    public function updateSchedules($name, $description)
    {
        // Versuche einen Eintrag mit dem gegebenen Namen zu finden
        $scheduleService = ScheduleService::where('service', $name)->where('description', $description)->first();

        if (!$scheduleService) {
            // Wenn der Eintrag nicht gefunden wurde, erstelle einen neuen Eintrag
            $scheduleService = ScheduleService::firstOrNew(['service' => $name, 'description' => $description]);
            $scheduleService->save();

            return "ScheduleService wurde erfolgreich erstellt!";
        }

        // Wenn der Eintrag gefunden wurde, aktualisiere den Zeitstempel
        $scheduleService->touch();

        return "ScheduleService wurde erfolgreich aktualisiert!";
    }

    public function getScheduleEntries()
    {
        $services = ScheduleService::all()->sortByDesc('updated_at');
        $formattedServices = [];
        foreach ($services as $service)
        {
            $updatedAt = Carbon::parse($service->updated_at);

            // Überprüfe, ob die Zeit weniger als eine Minute beträgt
            $formattedUpdatedAt = $updatedAt->diffInMinutes(Carbon::now()) < 1
                ? 'Ausführung gestartet'
                : $updatedAt->format('d.m.Y H:i:s');

            $formattedServices[] = [
                'id' => $service->id,
                'service' => $service->service,
                'description' => $service->description,
                'updated_at' => $formattedUpdatedAt
            ];
        }

        return json_encode($formattedServices);
    }
}
