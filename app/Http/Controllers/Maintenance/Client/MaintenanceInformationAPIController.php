<?php

namespace App\Http\Controllers\Maintenance\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\MaintenanceInformationAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceInformationResource;
use App\Models\Maintenance\Maintenance;
use Illuminate\Http\JsonResponse;

class MaintenanceInformationAPIController extends Controller
{
    /**
     * Obtener información completa del mantenimiento
     * Incluye logs, fotos, sugerencias de repuestos y toda la información relacionada
     *
     * @param Maintenance $maintenance
     * @param MaintenanceInformationAPIRequest $request
     * @return JsonResponse
     */
    public function maintenanceInformation(Maintenance $maintenance, MaintenanceInformationAPIRequest $request): JsonResponse
    {
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.device',
            'clientDevices.client',
            'technician.user',
            'photos',
            'actionLogs.technician.user',
            'sparePartSuggestions.clientDevice.device',
        ]);

        return $this->makeResponseResource(
            'Maintenance information retrieved successfully',
            new MaintenanceInformationResource($maintenance)
        );
    }
}

