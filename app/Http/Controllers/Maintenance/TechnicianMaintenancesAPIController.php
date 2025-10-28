<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\TechnicianMaintenancesAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\TechnicianMaintenancesCriteria;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenancesAPIController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $maintenanceRepository)
    {}

    public function index(TechnicianMaintenancesAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new TechnicianMaintenancesCriteria(
            technicianId: session('technician_id'),
            status: $request->input('status'),
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to')
        ));

        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('date_maintenance', 'asc'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at', 'asc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);

        $maintenances->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user'
        ]);

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));
    }
}
