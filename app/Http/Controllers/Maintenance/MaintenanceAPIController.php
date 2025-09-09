<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CreateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\DeleteMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ShowMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\Maintenance\ClientMaintenancesCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceAPIController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $maintenanceRepository)
    {}

    public function index(ReadMaintenanceAPIRequest $request): JsonResponse
    {
        $clientId = session('client_id');

        $this->maintenanceRepository->pushCriteria(new ClientMaintenancesCriteria($clientId));

        $clientDevice = $request->has('unpaginated') ?
            $this->maintenanceRepository->all($clientId) :
            $this->maintenanceRepository->paginate(20);
        $clientDevice->load(['clientDevice']);


        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($clientDevice));

    }

    public function store(CreateMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();
        $maintenance = $this->maintenanceRepository->create($input);

        return $this->makeResponseResource('Maintenance Created Successfully', new MaintenanceResource($maintenance));
    }
    public function show(Maintenance $maintenance, ShowMaintenanceAPIRequest $request): JsonResponse
    {
        $maintenance->load('clientDevice.device', 'technician.user');

        return $this->makeResponseResource('Maintenance retrieved Successfully', new MaintenanceResource($maintenance));
    }

    public function update(Maintenance $maintenance, UpdateMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->update($input,$maintenance->id,);

        return $this->makeResponseResource('Maintenance Updated Successfully', new MaintenanceResource($maintenance));
    }

    public function destroy(Maintenance $maintenance, DeleteMaintenanceAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->delete($maintenance->id);

        return $this->makeResponse('Maintenance Deleted Successfully',[$maintenance->id]);
    }
}
