<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CreateMaintenanceAPIRequest;
use App\Http\Requests\MaintenanceType\ReadMaintenanceTypeAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceTypeResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceTypeRepository;
use Illuminate\Http\JsonResponse;

class MaintenanceTypeAPIController extends Controller
{
    public function __construct(private readonly MaintenanceTypeRepository $maintenanceTypeRepository)
    {}

    public function index(ReadMaintenanceTypeAPIRequest $request): JsonResponse
    {

        $maintenanceType = $request->has('unpaginated') ?
            $this->maintenanceTypeRepository->all() :
            $this->maintenanceTypeRepository->paginate(20);
        $maintenanceType->load(['clientDevice']);


        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceTypeResource::collection($maintenanceType));

    }

    public function store(CreateMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();
        $maintenance = $this->maintenanceRepository->create($input);

        return $this->makeResponseResource('Maintenance Created Successfully', new MaintenanceTypeResource($maintenance));
    }
    public function show(Maintenance $maintenance, ShowMaintenanceTypeAPIRequest $request): JsonResponse
    {
        $maintenance->load('clientDevice.device', 'maintenanceType');

        return $this->makeResponseResource('Maintenance retrieved Successfully', new MaintenanceTypeResource($maintenance));
    }

    public function update(Maintenance $maintenance, UpdateMaintenanceTypeAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->update($input,$maintenance->id,);

        return $this->makeResponseResource('Maintenance Updated Successfully', new MaintenanceTypeResource($maintenance));
    }

    public function destroy(Maintenance $maintenance, DeleteMaintenanceTypeAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->delete($maintenance->id);

        return $this->makeResponse('Maintenance Deleted Successfully',[$maintenance->id]);
    }
}
