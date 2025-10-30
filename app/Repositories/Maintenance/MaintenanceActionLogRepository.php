<?php

namespace App\Repositories\Maintenance;

use App\Models\Maintenance\MaintenanceActionLog;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class MaintenanceActionLogRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'maintenance_id',
        'technician_id',
        'action',
        'timestamp',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MaintenanceActionLog::class;
    }

    /**
     * Crear un log de acción para un mantenimiento
     */
    public function createActionLog(
        int $maintenanceId, 
        int $technicianId, 
        string $action, 
        ?string $reason = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): MaintenanceActionLog {
        return $this->create([
            'maintenance_id' => $maintenanceId,
            'technician_id' => $technicianId,
            'action' => $action,
            'timestamp' => now(),
            'reason' => $reason,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Obtener logs de acción por mantenimiento
     */
    public function getActionLogsByMaintenance(int $maintenanceId): Collection
    {
        return $this->findWhere(['maintenance_id' => $maintenanceId])
            ->sortBy('timestamp');
    }

    /**
     * Obtener logs de acción por técnico
     */
    public function getActionLogsByTechnician(int $technicianId): Collection
    {
        return $this->findWhere(['technician_id' => $technicianId])
            ->sortBy('timestamp');
    }

    /**
     * Obtener logs de acción por acción específica
     */
    public function getActionLogsByAction(string $action): Collection
    {
        return $this->findWhere(['action' => $action])
            ->sortBy('timestamp');
    }

    /**
     * Obtener el último log de acción de un mantenimiento
     */
    public function getLatestActionLog(int $maintenanceId): ?MaintenanceActionLog
    {
        return $this->findWhere(['maintenance_id' => $maintenanceId])
            ->sortByDesc('timestamp')
            ->first();
    }

    /**
     * Obtener logs con coordenadas GPS
     */
    public function getLocationLogs(int $maintenanceId): Collection
    {
        return $this->findWhere(['maintenance_id' => $maintenanceId])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->sortBy('timestamp');
    }

    /**
     * Obtener la última ubicación de un mantenimiento
     */
    public function getLatestLocation(int $maintenanceId): ?array
    {
        $latestLog = $this->getLatestActionLog($maintenanceId);
        
        return $latestLog && $latestLog->hasLocation() ? $latestLog->location : null;
    }

    /**
     * Calcular duración entre dos acciones
     */
    public function calculateDuration(int $maintenanceId, string $startAction, string $endAction): ?string
    {
        $startLog = $this->findWhere(['maintenance_id' => $maintenanceId, 'action' => $startAction])
            ->sortBy('timestamp')
            ->first();

        $endLog = $this->findWhere(['maintenance_id' => $maintenanceId, 'action' => $endAction])
            ->sortBy('timestamp')
            ->first();

        if (!$startLog || !$endLog) {
            return null;
        }

        $duration = $startLog->timestamp->diff($endLog->timestamp);
        return $duration->format('%H:%I:%S');
    }

    /**
     * Obtener estadísticas de acciones por técnico
     */
    public function getTechnicianStats(int $technicianId): array
    {
        $logs = $this->getActionLogsByTechnician($technicianId);
        
        return [
            'total_actions' => $logs->count(),
            'actions_by_type' => $logs->groupBy('action')->map->count(),
            'maintenances_worked' => $logs->pluck('maintenance_id')->unique()->count(),
        ];
    }
}
