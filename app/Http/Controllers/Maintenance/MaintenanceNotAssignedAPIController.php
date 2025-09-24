<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\AssignMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceNotAssignedAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceNotAssignedAPIController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $maintenanceRepository)
    {}

    public function __invoke(ReadMaintenanceNotAssignedAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'pending'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $maintenances->load(['clientDevice']);


        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));

    }
    public function assignMaintenance(Maintenance $maintenance, AssignMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->assignMaintenance($maintenance, $input);
        $maintenance->load(['clientDevice']);

        return $this->makeResponseResource('Maintenance assigned Successfully', new MaintenanceResource($maintenance));
    }
}
