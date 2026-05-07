<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\TechnicianMaintenanceDetailRequest;
use App\Http\Requests\Technician\TechnicianMaintenancesListRequest;
use App\Http\Resources\Maintenance\MaintenanceActionLogResource;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenancesDetailAPIController extends Controller
{
    public function __construct(
        private readonly TechnicianRepository $technicianRepository,
        private readonly MaintenanceRepository $maintenanceRepository
    ) {}


    public function index(Technician $technician, TechnicianMaintenancesListRequest $request): JsonResponse
    {
        // Construir filtros - usar '=' en lugar de 'is' para IDs numéricos
        $filters = ['technician_id|=|' . $technician->id];

        // Aplicar filtros adicionales si existen
        if ($request->has('status')) {
            $filters[] = 'status|is|' . $request->input('status');
        }

        if ($request->has('date_from')) {
            $filters[] = 'date_maintenance|>=|' . $request->input('date_from');
        }

        if ($request->has('date_to')) {
            $filters[] = 'date_maintenance|<=|' . $request->input('date_to');
        }

        // Aplicar filtros
        $this->maintenanceRepository->pushCriteria(new FiltersCriteria(implode(';', $filters)));

        // Ordenar por fecha
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('date_maintenance', 'desc'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at', 'desc'));

        // Obtener mantenimientos
        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);

        // Cargar relaciones necesarias
        $maintenances->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice'
        ]);

        return $this->makeResponseResource(
            'Technician maintenances retrieved successfully',
            MaintenanceResource::collection($maintenances)
        );
    }


    public function show(Technician $technician, Maintenance $maintenance, TechnicianMaintenanceDetailRequest $request): JsonResponse
    {
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'actionLogs' => function ($query) {
                $query->orderBy('timestamp', 'desc');
            },
            'sparePartSuggestions.clientDevice.device'
        ]);

        // Obtener información de control detallada
        $lastLocation = $this->technicianRepository->getLastLocation($technician->id);
        $maintenanceTime = $this->technicianRepository->getCurrentMaintenanceTime($maintenance->id);
        $progressPercentage = $this->technicianRepository->getMaintenanceProgressPercentage($maintenance->id);
        $logs = $maintenance->actionLogs;

        // Obtener progreso por dispositivo
        $devicesProgress = $maintenance->clientDevices->map(function ($cd) {
            $indices = $cd->pivot->progress_completed_indices;
            if (is_string($indices)) {
                $decoded = json_decode($indices, true);
                $indices = is_array($decoded) ? $decoded : [];
            }
            $indices = is_array($indices) ? array_values(array_unique(array_map('intval', $indices))) : [];
            sort($indices);

            return [
                'client_device_id' => $cd->id,
                'device' => [
                    'id' => $cd->device->id,
                    'brand' => $cd->device->brand,
                    'model' => $cd->device->model,
                    'type' => $cd->device->type,
                    'serial' => $cd->serial,
                ],
                'completed_indices' => $indices,
                'progress_total' => (int) ($cd->pivot->progress_total ?? 0),
                'progress_completed_count' => (int) ($cd->pivot->progress_completed_count ?? 0),
                'progress_pct' => (float) ($cd->pivot->progress_pct ?? 0),
                'progress_status' => $cd->pivot->progress_status ?? 'pending',
                'progress_updated_at' => $cd->pivot->progress_updated_at,
            ];
        })->values();

        // Preparar respuesta detallada
        $detailData = [
            'maintenance' => new MaintenanceResource($maintenance),
            'control_info' => [
                'last_location' => $lastLocation,
                'maintenance_time' => $maintenanceTime,
                'progress_percentage' => $progressPercentage,
                'devices_progress' => $devicesProgress,
                'total_devices' => $devicesProgress->count(),
                'avg_progress_pct' => round($devicesProgress->avg('progress_pct') ?? 0, 2),
                'all_completed' => $devicesProgress->count() > 0 && $devicesProgress->every(function ($d) {
                    return ($d['progress_status'] ?? '') === 'completed';
                }),
            ],
            'logs' => MaintenanceActionLogResource::collection($logs),
            'total_logs' => $logs->count(),
        ];

        return $this->makeResponseResource(
            'Technician maintenance detail retrieved successfully',
            $detailData
        );
    }
}

