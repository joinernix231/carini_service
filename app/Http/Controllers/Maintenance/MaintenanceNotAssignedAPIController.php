<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ReadMaintenanceNotAssignedAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceNotAssignedAPIController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $maintenanceRepository)
    {}

    public function __invoke(ReadMaintenanceNotAssignedAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'pending'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at','asc'));


        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $maintenances->load([
            'client',
            'clientDevice.device'
        ]);

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));

    }
}
