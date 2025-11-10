<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SaveProgressRequest;
use App\Models\Maintenance\Maintenance;
use App\Services\MaintenanceProgressService;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenanceProgressAPIController extends Controller
{
    /**
     * Guardar progreso por equipos (batch)
     *
     * @param Maintenance $maintenance
     * @param SaveProgressRequest $request
     * @return JsonResponse
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
     *
     * @param Maintenance $maintenance
     * @return JsonResponse
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

