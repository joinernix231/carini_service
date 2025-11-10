<?php

namespace App\Http\Controllers\Maintenance\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ConfirmMaintenanceAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use Illuminate\Http\JsonResponse;

class MaintenanceConfirmationAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    /**
     * Confirmar mantenimiento por parte del cliente
     *
     * @param Maintenance $maintenance
     * @param ConfirmMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function confirm(Maintenance $maintenance, ConfirmMaintenanceAPIRequest $request): JsonResponse
    {
        $maintenance = $this->maintenanceRepository->confirm($maintenance);

        // Cargar relaciones adicionales para la respuesta
        $maintenance->load('clientDevices.device', 'clientDevices.client', 'technician.user');

        return $this->makeResponseResource(
            'Maintenance confirmed successfully',
            new MaintenanceResource($maintenance)
        );
    }
}

