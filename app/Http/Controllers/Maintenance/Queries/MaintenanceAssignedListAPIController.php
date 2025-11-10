<?php

namespace App\Http\Controllers\Maintenance\Queries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ReadMaintenanceAssignedAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceAssignedListAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function __invoke(ReadMaintenanceAssignedAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'assigned'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at','asc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        
        $maintenances->load([
            'clientDevices.client',
            'clientDevices.device'
        ]);

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));
    }
}

