<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMaintenanceAPIRequest;
use App\Http\Requests\ReadMaintenanceAPIRequest;
use App\Http\Requests\ShowMaintenanceAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceAPIController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $maintenanceRepository)
    {}

    public function index(ReadMaintenanceAPIRequest $request): JsonResponse
    {
        $clientDevice = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $clientDevice->load(['client','device']);


        return $this->makeResponseResource('ClientsDevices retrieved Successfully', MaintenanceResource::collection($clientDevice));

    }

    public function store(CreateMaintenanceAPIRequest $request): JsonResponse
    {


    }
    public function show(Maintenance $maintenance, ShowMaintenanceAPIRequest $request): JsonResponse
    {



        return $this->makeResponseResource('ClientDevice retrieved Successfully', new MaintenanceResource($linkDevice));
    }

    public function update(ClientDevice $linkDevice, UpdateClientDeviceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $clientDevice = $this->maintenanceRepository->update($input,$linkDevice->id,);

        return $this->makeResponseResource('ClientDevice Updated Successfully', new MaintenanceResource($clientDevice));
    }

    public function destroy(ClientDevice $linkDevice, DeleteClientDeviceAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->delete($linkDevice->id);

        return $this->makeResponse('ClientDevice Deleted Successfully',[]);
    }
}
