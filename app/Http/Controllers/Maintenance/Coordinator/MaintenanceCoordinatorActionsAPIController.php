<?php

namespace App\Http\Controllers\Maintenance\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\MarkAsCalledAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use Illuminate\Http\JsonResponse;

class MaintenanceCoordinatorActionsAPIController extends Controller
{
    /**
     * Marcar como llamado por coordinador
     *
     * @param Maintenance $maintenance
     * @param MarkAsCalledAPIRequest $request
     * @return JsonResponse
     */
    public function markAsCalled(Maintenance $maintenance, MarkAsCalledAPIRequest $request): JsonResponse
    {
        $maintenance->coordinator_called = true;
        $maintenance->coordinator_called_at = now();
        $maintenance->save();

        $maintenance->load('clientDevices.device', 'clientDevices.client', 'technician.user');

        return $this->makeResponseResource(
            'Marked as called successfully',
            new MaintenanceResource($maintenance)
        );
    }
}

