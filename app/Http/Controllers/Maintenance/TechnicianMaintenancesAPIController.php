<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ActiveMaintenanceRequest;
use App\Http\Requests\Maintenance\PauseMaintenanceRequest;
use App\Http\Requests\Maintenance\ResumeMaintenanceRequest;
use App\Http\Requests\Maintenance\ShowTechnicianMaintenanceRequest;
use App\Http\Requests\Maintenance\StartMaintenanceRequest;
use App\Http\Requests\Maintenance\SaveProgressRequest;
use App\Http\Requests\Maintenance\TechnicianMaintenancesAPIRequest;
use App\Http\Requests\Maintenance\UploadMultiplePhotosRequest;
use App\Http\Resources\Maintenance\MaintenancePhotoResource;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceActionLogRepository;
use App\Repositories\Maintenance\MaintenancePhotoRepository;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Services\MaintenanceProgressService;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\TechnicianMaintenancesCriteria;
use App\Utils\Resources\ResourceService;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenancesAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;
    private MaintenancePhotoRepository $maintenancePhotoRepository;
    private MaintenanceActionLogRepository $maintenanceActionLogRepository;
    private ResourceService $resourceService;

    public function __construct(
        MaintenanceRepository $maintenanceRepository,
        MaintenancePhotoRepository $maintenancePhotoRepository,
        MaintenanceActionLogRepository $maintenanceActionLogRepository,
        ResourceService $resourceService
    ) {
        $this->maintenanceRepository = $maintenanceRepository;
        $this->maintenancePhotoRepository = $maintenancePhotoRepository;
        $this->maintenanceActionLogRepository = $maintenanceActionLogRepository;
        $this->resourceService = $resourceService;
    }

    public function index(TechnicianMaintenancesAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new TechnicianMaintenancesCriteria(
            session('technician_id'),
            $request->input('status'),
            $request->input('date_from'),
            $request->input('date_to')
        ));

        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('date_maintenance', 'asc'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at', 'asc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);

        $maintenances->load([
            'clientDevices.client',
            'clientDevices.device'
        ]);

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));
    }

    public function show(Maintenance $maintenance, ShowTechnicianMaintenanceRequest $request): JsonResponse
    {
        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice'
        ]);

        return $this->makeResponseResource('Maintenance detail retrieved successfully', new MaintenanceResource($maintenance));
    }

    public function uploadPhoto(Maintenance $maintenance, UploadMultiplePhotosRequest $request): JsonResponse
    {
        $photosData = $request->validated()['photos'];

        // Agregar maintenance_id a cada foto
        foreach ($photosData as &$photoData) {
            $photoData['maintenance_id'] = $maintenance->id;
        }

        // Crear múltiples fotos usando el método del repositorio
        $createdPhotos = $this->maintenancePhotoRepository->createMultiplePhotos($maintenance->id, $photosData);

        return $this->makeResponseResource('Maintenance Photos uploaded successfully', MaintenancePhotoResource::collection($createdPhotos));
    }

    public function startMaintenance(Maintenance $maintenance, StartMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();
        
        // Actualizar estado del mantenimiento
        $maintenance = $this->maintenanceRepository->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ], $maintenance->id);

        // Registrar acción con ubicación
        $this->maintenanceActionLogRepository->createActionLog(
            $maintenance->id,
            session('technician_id'),
            'start',
            null,
            $input['latitude'],
            $input['longitude']
        );

        return $this->makeResponseResource('Maintenance started successfully', new MaintenanceResource($maintenance));
    }

    /**
     * Verificar si el técnico tiene un mantenimiento activo
     *
     * @param ActiveMaintenanceRequest $request
     * @return JsonResponse
     */
    public function activeMaintenance(ActiveMaintenanceRequest $request): JsonResponse
    {
        $technicianId = session('technician_id');

        // Buscar mantenimiento activo del técnico
        $activeMaintenance = $this->maintenanceRepository->findWhere([
            'technician_id' => $technicianId,
            'status' => 'in_progress'
        ])->first();

        $hasActiveMaintenance = $activeMaintenance !== null;

        $responseData = [
            'has_active_maintenance' => $hasActiveMaintenance,
            'maintenance' => null
        ];

        if ($hasActiveMaintenance) {
            $activeMaintenance->load([
                'clientDevices.device',
                'technician.user',
                'photos.clientDevice'
            ]);

            $responseData['maintenance'] = new MaintenanceResource($activeMaintenance);
        }

        return $this->makeResponseResource('Active maintenance status retrieved successfully', $responseData);
    }

    /**
     * Pausar un mantenimiento en progreso
     *
     * @param Maintenance $maintenance
     * @param PauseMaintenanceRequest $request
     * @return JsonResponse
     */
    public function pauseMaintenance(Maintenance $maintenance, PauseMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();
        
        // Calcular duración del trabajo hasta ahora
        $startedAt = $maintenance->started_at;
        $pausedAt = now();
        $duration = $startedAt ? $startedAt->diff($pausedAt)->format('%H:%I:%S') : '00:00:00';
        
        // Actualizar estado del mantenimiento
        $maintenance = $this->maintenanceRepository->update([
            'status' => 'assigned',
        ], $maintenance->id);

        // Registrar acción con ubicación y razón
        $this->maintenanceActionLogRepository->createActionLog(
            $maintenance->id,
            session('technician_id'),
            'pause',
            $input['pause_reason'] ?? null,
            $input['latitude'],
            $input['longitude']
        );

        return $this->makeResponseResource('Maintenance paused successfully', [
            'id' => $maintenance->id,
            'status' => $maintenance->status,
            'paused_at' => $pausedAt,
            'pause_duration' => $duration,
        ]);
    }

    /**
     * Reanudar un mantenimiento pausado
     *
     * @param Maintenance $maintenance
     * @param ResumeMaintenanceRequest $request
     * @return JsonResponse
     */
    public function resumeMaintenance(Maintenance $maintenance, ResumeMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();
        
        // Actualizar estado del mantenimiento
        $maintenance = $this->maintenanceRepository->update([
            'status' => 'in_progress',
        ], $maintenance->id);

        // Registrar acción con ubicación
        $this->maintenanceActionLogRepository->createActionLog(
            $maintenance->id,
            session('technician_id'),
            'resume',
            null,
            $input['latitude'],
            $input['longitude']
        );

        return $this->makeResponseResource('Maintenance resumed successfully', [
            'id' => $maintenance->id,
            'status' => $maintenance->status,
            'resumed_at' => now(),
        ]);
    }

    /**
     * Finalizar un mantenimiento
     *
     * @param Maintenance $maintenance
     * @param ResumeMaintenanceRequest $request
     * @return JsonResponse
     */
    public function endMaintenance(Maintenance $maintenance, ResumeMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();
        
        // Actualizar estado del mantenimiento
        $maintenance = $this->maintenanceRepository->update([
            'status' => 'completed',
        ], $maintenance->id);

        // Registrar acción con ubicación
        $this->maintenanceActionLogRepository->createActionLog(
            $maintenance->id,
            session('technician_id'),
            'end',
            null,
            $input['latitude'],
            $input['longitude']
        );

        return $this->makeResponseResource('Maintenance completed successfully', [
            'id' => $maintenance->id,
            'status' => $maintenance->status,
            'completed_at' => now(),
            'total_work_time' => $maintenance->getTotalWorkTime(),
        ]);
    }

    /**
     * Guardar progreso por equipos (batch)
     */
    public function saveProgress(Maintenance $maintenance, SaveProgressRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance->load(['clientDevices.device']);
        $byId = $maintenance->clientDevices->keyBy('id');

        /** @var MaintenanceProgressService $service */
        $service = app(MaintenanceProgressService::class);

        $devicesResponse = [];
        foreach ($input['devices'] as $dev) {
            $clientDeviceId = (int) ($dev['client_device_id'] ?? 0);
            $completed = array_key_exists('completed_indices', $dev) ? $dev['completed_indices'] : null;

            $device = $byId->get($clientDeviceId);
            $type = $device ? ($device->device->type ?? null) : null;

            // Si completed_indices es null, se interpreta como RESET (poner todo en null)
            if ($completed === null) {
                $maintenance->clientDevices()->updateExistingPivot($clientDeviceId, [
                    'progress_completed_indices' => null,
                    'progress_completed_count' => null,
                    'progress_total' => null,
                    'progress_pct' => null,
                    'progress_status' => null,
                    'progress_updated_at' => now(),
                ]);

                $devicesResponse[] = [
                    'client_device_id' => $clientDeviceId,
                    'progress_total' => null,
                    'progress_completed_count' => null,
                    'progress_pct' => null,
                    'progress_status' => null,
                ];
                continue;
            }

            // completed_indices llegó (puede ser []) -> calcular normalmente
            $total = $service->getTotalByType($type, $dev['items_total'] ?? null) ?? 0;
            $cleanCompleted = $service->sanitizeCompleted($completed, $total);
            $count = count($cleanCompleted);
            $pct = $service->computePct($count, $total);
            $status = $service->computeStatus($count, $total);

            $maintenance->clientDevices()->updateExistingPivot($clientDeviceId, [
                'progress_completed_indices' => json_encode($cleanCompleted),
                'progress_completed_count' => $count,
                'progress_total' => $total,
                'progress_pct' => $pct,
                'progress_status' => $status,
                'progress_updated_at' => now(),
            ]);

            $devicesResponse[] = [
                'client_device_id' => $clientDeviceId,
                'progress_total' => $total,
                'progress_completed_count' => $count,
                'progress_pct' => $pct,
                'progress_status' => $status,
            ];
        }

        // Recalcular global a partir del pivot actual
        $maintenance->load('clientDevices');
        $collection = $maintenance->clientDevices;
        $avg = round($collection->avg(function ($cd) {
            return (float) ($cd->pivot->progress_pct ?? 0);
        }) ?? 0, 2);
        $allCompleted = $collection->count() > 0 && $collection->every(function ($cd) {
            return ($cd->pivot->progress_status ?? '') === 'completed';
        });

        $global = [
            'total_devices' => $collection->count(),
            'avg_progress_pct' => $avg,
            'all_completed' => (bool) $allCompleted,
        ];

        return $this->makeResponseResource('Progress saved', [
            'maintenance_id' => $maintenance->id,
            'devices' => $devicesResponse,
            'global' => $global,
        ]);
    }

    /**
     * Obtener progreso para hidratar la pantalla
     */
    public function getProgress(Maintenance $maintenance): JsonResponse
    {
        $maintenance->load('clientDevices');
        $devicesOut = $maintenance->clientDevices->map(function ($cd) {
            $indices = $cd->pivot->progress_completed_indices;
            if (is_string($indices)) {
                $decoded = json_decode($indices, true);
                $indices = is_array($decoded) ? $decoded : [];
            }
            // Normalizar: array numérico, único y ordenado
            $indices = is_array($indices) ? array_values(array_unique(array_map('intval', $indices))) : [];
            sort($indices);

            return [
                'client_device_id' => $cd->id,
                'completed_indices' => $indices,
                'progress_total' => (int) ($cd->pivot->progress_total ?? 0),
                'progress_completed_count' => (int) ($cd->pivot->progress_completed_count ?? 0),
                'progress_pct' => (float) ($cd->pivot->progress_pct ?? 0),
                'progress_status' => $cd->pivot->progress_status ?? 'pending',
            ];
        })->values();

        $avg = round($devicesOut->avg('progress_pct') ?? 0, 2);
        $allCompleted = $devicesOut->count() > 0 && $devicesOut->every(function ($d) {
            return ($d['progress_status'] ?? '') === 'completed';
        });

        return $this->makeResponseResource('Progress retrieved', [
            'devices' => $devicesOut,
            'global' => [
                'total_devices' => $devicesOut->count(),
                'avg_progress_pct' => $avg,
                'all_completed' => (bool) $allCompleted,
            ],
        ]);
    }
}
