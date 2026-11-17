<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Events\Maintenance\MaintenanceCompleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ActiveMaintenanceRequest;
use App\Http\Requests\Maintenance\CompleteMaintenanceRequest;
use App\Http\Requests\Maintenance\OnTheWayMaintenanceRequest;
use App\Http\Requests\Maintenance\PauseMaintenanceRequest;
use App\Http\Requests\Maintenance\ResumeMaintenanceRequest;
use App\Http\Requests\Maintenance\StartMaintenanceRequest;
use App\Http\Resources\Maintenance\MaintenanceActionLogResource;
use App\Http\Resources\Maintenance\MaintenancePhotoResource;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Notifications\TecnicoEnCamino;
use App\Repositories\Maintenance\MaintenanceActionLogRepository;
use App\Repositories\Maintenance\MaintenancePhotoRepository;
use App\Repositories\Maintenance\MaintenanceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TechnicianMaintenanceActionsAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;
    private MaintenanceActionLogRepository $maintenanceActionLogRepository;
    private MaintenancePhotoRepository $maintenancePhotoRepository;

    public function __construct(
        MaintenanceRepository $maintenanceRepository,
        MaintenanceActionLogRepository $maintenanceActionLogRepository,
        MaintenancePhotoRepository $maintenancePhotoRepository
    ) {
        $this->maintenanceRepository = $maintenanceRepository;
        $this->maintenanceActionLogRepository = $maintenanceActionLogRepository;
        $this->maintenancePhotoRepository = $maintenancePhotoRepository;
    }

    public function onTheWay(Maintenance $maintenance, OnTheWayMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->update([
            'status' => 'in_progress',
        ], $maintenance->id);

        $actionLog = $this->maintenanceActionLogRepository->createActionLog(
            $maintenance->id,
            session('technician_id'),
            'on_the_way',
            null,
            $input['latitude'],
            $input['longitude']
        );

        // Cargar relaciones necesarias para la notificación
        $maintenance->load([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user'
        ]);

        // Enviar notificación al cliente
        $client = $maintenance->client;
        if ($client && $client->user) {
            try {
                $client->user->notify(new TecnicoEnCamino($maintenance));
            } catch (\Exception $e) {
                Log::error('Error enviando notificación de técnico en camino al cliente: ' . $e->getMessage(), [
                    'maintenance_id' => $maintenance->id,
                    'client_id' => $client->id,
                ]);
            }
        } else {
            Log::warning('No se pudo enviar notificación de técnico en camino: cliente o usuario no encontrado', [
                'maintenance_id' => $maintenance->id,
            ]);
        }

        return $this->makeResponseResource('Mantenimiento marcado como \'En Camino\' exitosamente', new MaintenanceActionLogResource($actionLog));
    }

    public function startMaintenance(Maintenance $maintenance, StartMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ], $maintenance->id);

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

    public function activeMaintenance(ActiveMaintenanceRequest $request): JsonResponse
    {
        $technicianId = session('technician_id');

        $activeMaintenance = $this->maintenanceRepository->getActiveMaintenanceByTechnician($technicianId);

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

    public function pauseMaintenance(Maintenance $maintenance, PauseMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();

        $startedAt = $maintenance->started_at;
        $pausedAt = now();
        $duration = $startedAt ? $startedAt->diff($pausedAt)->format('%H:%I:%S') : '00:00:00';

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

    public function resumeMaintenance(Maintenance $maintenance, ResumeMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();


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
     * Completar un mantenimiento
     *
     * @param Maintenance $maintenance
     * @param CompleteMaintenanceRequest $request
     * @return JsonResponse
     */
    public function complete(Maintenance $maintenance, CompleteMaintenanceRequest $request): JsonResponse
    {
        $input = $request->validated();

        // Preparar datos para actualizar el mantenimiento
        $updateData = [
            'status' => 'completed',
            'observations' => $input['final_observations'] ?? null,
            'signature_photo' => $input['client_signature'] ?? null,
        ];

        $maintenance = $this->maintenanceRepository->update($updateData, $maintenance->id);

        $finalPhotos = $this->maintenancePhotoRepository->createFinalPhotos($maintenance->id, $input['final_photos'] ?? []
        );

        // Registrar acción con ubicación
        $this->maintenanceActionLogRepository->createActionLog(
            $maintenance->id,
            session('technician_id'),
            'end',
            null,
            $input['latitude'],
            $input['longitude']
        );

        $maintenance->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice'
        ]);

        event(new MaintenanceCompleted($maintenance));

        return $this->makeResponseResource('Maintenance completed successfully', [
            'id' => $maintenance->id,
            'status' => $maintenance->status,
            'completed_at' => now(),
            'total_work_time' => $maintenance->getTotalWorkTime(),
            'final_photos' => MaintenancePhotoResource::collection($finalPhotos),
        ]);
    }
}

