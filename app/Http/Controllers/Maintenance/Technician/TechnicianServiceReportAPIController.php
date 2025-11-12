<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SaveServiceReportRequest;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceServiceReportRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TechnicianServiceReportAPIController extends Controller
{
    public function __construct(
        private readonly MaintenanceServiceReportRepository $serviceReportRepository
    ) {}

    /**
     * Guardar o actualizar el reporte de servicio
     *
     * @param Maintenance $maintenance
     * @param SaveServiceReportRequest $request
     * @return JsonResponse
     */
    public function store(Maintenance $maintenance, SaveServiceReportRequest $request): JsonResponse
    {
        $input = $request->validated();
        
        $serviceReport = $this->serviceReportRepository->createOrUpdate($input, $maintenance->id);
        
        return $this->makeResponse('Service report saved successfully', [
            'id' => $serviceReport->id,
            'maintenance_id' => $serviceReport->maintenance_id,
            'version' => $serviceReport->version,
        ]);
    }

    /**
     * Obtener el reporte de servicio
     *
     * @param Maintenance $maintenance
     * @return JsonResponse
     */
    public function show(Maintenance $maintenance): JsonResponse
    {
        $serviceReport = $maintenance->serviceReport;
        
        if (!$serviceReport) {
            return $this->makeResponse('Service report not found', [], 404);
        }
        
        return $this->makeResponse('Service report retrieved successfully', $serviceReport);
    }
}

