<?php

namespace App\Repositories\Technician;

use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\BaseRepository;
use Illuminate\Support\Carbon;


class TechnicianRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Technician::class;
    }

    public function getAvailableTechnicianForShift(string $date): ?array
    {
        $technicians = $this->model
            ->where('status', 'active')
            ->with(['maintenances' => function ($query) use ($date) {
                $query->whereDate('date_maintenance', $date);
            }])
            ->get();

        foreach ($technicians as $technician) {
            $shifts = $technician->maintenances->pluck('shift')->toArray();

            $hasAM = in_array('AM', $shifts);
            $hasPM = in_array('PM', $shifts);

            if (!$hasAM) {
                return ['technician' => $technician, 'shift' => 'AM'];
            }

            if (!$hasPM) {
                return ['technician' => $technician, 'shift' => 'PM'];
            }
        }

        return null;
    }

    public function getAvailableDates(int $dias = 15): array
    {
        $today = Carbon::today();
        $availableDates = [];

        for ($i = 1; $i < $dias; $i++) {
            $date = $today->copy()->addDays($i);

            if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                continue;
            }

            $available = $this->getAvailableTechnicianForShift($date->format('Y-m-d'));

            if ($available !== null) {
                $availableDates[] = $date->format('Y-m-d');
            }
        }

        return $availableDates;
    }

    /**
     * Obtener técnicos disponibles para una fecha y turno específicos
     *
     * @param string|null $date Fecha en formato Y-m-d
     * @param string|null $shift Turno (AM o PM)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTechnicians(?string $date = null, ?string $shift = null)
    {
        // Obtener todos los técnicos activos con rol 'tecnico'
        $query = $this->model
            ->where('status', 'active')
            ->whereHas('user', function ($q) {
                $q->where('role', 'tecnico');
            })
            ->with('user');

        // Si no se proporcionan fecha y turno, retornar todos los técnicos
        if (!$date || !$shift) {
            return $query->get();
        }

        // Obtener IDs de técnicos que tienen mantenimientos activos para la fecha y turno específicos
        // Un técnico está ocupado si tiene un mantenimiento asignado para esa fecha/turno Y está activo
        $maintenances = Maintenance::where('date_maintenance', $date)
            ->where('shift', $shift)
            ->whereNotNull('technician_id')
            ->with('actionLogs')
            ->get();

        // Filtrar mantenimientos activos (basado en action logs)
        $busyTechnicianIds = $maintenances
            ->filter(function ($maintenance) {
                return $maintenance->isActive();
            })
            ->pluck('technician_id')
            ->filter()
            ->unique()
            ->toArray();

        // Excluir técnicos ocupados
        if (!empty($busyTechnicianIds)) {
            $query->whereNotIn('id', $busyTechnicianIds);
        }

        return $query->get();
    }

    public function getTechnicianByUserId(int $userId): ?Technician
    {
        return $this->model
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Obtener el mantenimiento actual (activo) del técnico
     * Basado en el último action log, no en el status
     */
    public function getCurrentMaintenance(int $technicianId): ?Maintenance
    {
        $maintenances = Maintenance::where('technician_id', $technicianId)
            ->with(['clientDevices', 'actionLogs'])
            ->get();

        // Buscar el mantenimiento activo basado en el último action log
        return $maintenances->first(function ($maintenance) {
            return $maintenance->isActive();
        });
    }

    /**
     * Obtener la última ubicación del técnico desde los logs
     */
    public function getLastLocation(int $technicianId): ?array
    {
        $lastLog = \App\Models\Maintenance\MaintenanceActionLog::where('technician_id', $technicianId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('timestamp', 'desc')
            ->first();

        if (!$lastLog) {
            return null;
        }

        return [
            'latitude' => $lastLog->latitude,
            'longitude' => $lastLog->longitude,
            'timestamp' => $lastLog->timestamp,
        ];
    }

    /**
     * Calcular el tiempo que lleva el técnico en el mantenimiento actual
     */
    public function getCurrentMaintenanceTime(int $maintenanceId): ?array
    {
        $maintenance = Maintenance::find($maintenanceId);
        if (!$maintenance) {
            return null;
        }

        // Obtener el primer log de start
        $startLog = \App\Models\Maintenance\MaintenanceActionLog::where('maintenance_id', $maintenanceId)
            ->where('action', 'start')
            ->orderBy('timestamp', 'asc')
            ->first();

        if (!$startLog) {
            return null;
        }

        // Obtener el último log
        $lastLog = \App\Models\Maintenance\MaintenanceActionLog::where('maintenance_id', $maintenanceId)
            ->orderBy('timestamp', 'desc')
            ->first();

        if (!$lastLog) {
            return null;
        }

        // Determinar el tiempo final
        $endTime = $lastLog->action === 'end' ? $lastLog->timestamp : now();

        // Calcular tiempo total desde el start hasta el tiempo final
        $totalTimeMs = $startLog->timestamp->diffInMilliseconds($endTime);

        // Restar el tiempo pausado total (el método getTotalPausedTimeMs ya maneja pausas activas)
        $pausedTimeMs = $maintenance->getTotalPausedTimeMs();
        $totalTimeMs -= $pausedTimeMs;

        // Asegurar que el tiempo no sea negativo
        if ($totalTimeMs < 0) {
            $totalTimeMs = 0;
        }

        // Convertir milisegundos a formato legible
        $seconds = floor($totalTimeMs / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;

        return [
            'total_ms' => $totalTimeMs,
            'formatted' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
        ];
    }

    /**
     * Obtener el porcentaje de completado del mantenimiento
     */
    public function getMaintenanceProgressPercentage(int $maintenanceId): float
    {
        $maintenance = Maintenance::find($maintenanceId);
        if (!$maintenance) {
            return 0.0;
        }

        $maintenance->load('clientDevices');
        $clientDevices = $maintenance->clientDevices;

        if ($clientDevices->isEmpty()) {
            return 0.0;
        }

        // Calcular el promedio de los porcentajes de progreso de todos los dispositivos
        $avg = round($clientDevices->avg(function ($cd) {
            return (float) ($cd->pivot->progress_pct ?? 0);
        }) ?? 0, 2);

        return $avg;
    }

    /**
     * Obtener todos los logs del técnico
     */
    public function getTechnicianLogs(int $technicianId)
    {
        return \App\Models\Maintenance\MaintenanceActionLog::where('technician_id', $technicianId)
            ->with(['maintenance', 'technician'])
            ->orderBy('timestamp', 'desc')
            ->get();
    }

}
