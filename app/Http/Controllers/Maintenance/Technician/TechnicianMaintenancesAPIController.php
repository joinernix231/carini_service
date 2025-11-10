<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ShowTechnicianMaintenanceRequest;
use App\Http\Requests\Maintenance\TechnicianMaintenancesAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\TechnicianMaintenancesCriteria;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenancesAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
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
}

