<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\AssignMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceAssignedAPIRequest;
use App\Http\Requests\Maintenance\VerifyPaymentMaintenanceAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceAssignedAPIController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $maintenanceRepository)
    {}

    public function __invoke(ReadMaintenanceAssignedAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'assigned'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $maintenances->load([
            'client',
            'clientDevice.device'
        ]);

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));

    }
    public function assignMaintenance(Maintenance $maintenance, AssignMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->assignMaintenance($maintenance, $input);
        $maintenance->load(['clientDevice']);

        return $this->makeResponseResource('Maintenance assigned Successfully', new MaintenanceResource($maintenance));
    }
    public function verifyPaymentOfMaintenance(Maintenance $maintenance, VerifyPaymentMaintenanceAPIRequest $request): JsonResponse
    {
        $maintenance = $this->maintenanceRepository->verifyPayment($maintenance);

        return $this->makeResponseResource('Maintenance assigned Successfully', new MaintenanceResource($maintenance));
    }
}
