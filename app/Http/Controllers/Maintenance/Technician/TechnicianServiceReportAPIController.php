<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SaveServiceReportRequest;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceClientDevice;
use App\Repositories\Maintenance\MaintenanceServiceReportRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TechnicianServiceReportAPIController extends Controller
{
    private MaintenanceServiceReportRepository $serviceReportRepository;

    public function __construct(MaintenanceServiceReportRepository $serviceReportRepository)
    {
        $this->serviceReportRepository = $serviceReportRepository;
    }

    /**
     * Guardar o actualizar el reporte de servicio para un dispositivo específico
     *
     * @param Maintenance $maintenance
     * @param ClientDevice $clientDevice
     * @param SaveServiceReportRequest $request
     * @return JsonResponse
     */
    public function store(Maintenance $maintenance, ClientDevice $clientDevice, SaveServiceReportRequest $request): JsonResponse
    {
        // Verificar que el dispositivo pertenece al mantenimiento
        $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $maintenance->id)
            ->where('client_device_id', $clientDevice->id)
            ->first();

        if (!$maintenanceClientDevice) {
            return $this->makeResponse('El dispositivo no está asociado a este mantenimiento', [], 404);
        }

        $input = $request->validated();
        
        $serviceReport = $this->serviceReportRepository->createOrUpdate($input, $maintenanceClientDevice->id);
        
        return $this->makeResponse('Service report saved successfully', [
            'id' => $serviceReport->id,
            'maintenance_client_device_id' => $serviceReport->maintenance_client_device_id,
            'version' => $serviceReport->version,
        ]);
    }

    /**
     * Obtener el reporte de servicio para un dispositivo específico
     *
     * @param Maintenance $maintenance
     * @param ClientDevice $clientDevice
     * @return JsonResponse
     */
    public function show(Maintenance $maintenance, ClientDevice $clientDevice): JsonResponse
    {
        // Verificar que el dispositivo pertenece al mantenimiento
        $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $maintenance->id)
            ->where('client_device_id', $clientDevice->id)
            ->first();

        if (!$maintenanceClientDevice) {
            return $this->makeResponse('El dispositivo no está asociado a este mantenimiento', [], 404);
        }

        $serviceReport = $maintenanceClientDevice->serviceReport;
        
        if (!$serviceReport) {
            return $this->makeResponse('Service report not found', [], 404);
        }
        
        return $this->makeResponse('Service report retrieved successfully', $serviceReport->toArray());
    }
}

