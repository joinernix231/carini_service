<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CreateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\DeleteMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ShowMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateQuotationAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\ClientMaintenancesCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function index(ReadMaintenanceAPIRequest $request)
    {
        $clientId = session('client_id');

        $this->maintenanceRepository->pushCriteria(new ClientMaintenancesCriteria($clientId));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at','asc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $maintenances->load(['clientDevices.device', 'clientDevices.client']);


        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));

    }

    public function store(CreateMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $clientDevices = $input['client_devices'];

        $maintenance = $this->maintenanceRepository->create($input);
        $this->maintenanceRepository->AssociateMaintenanceDevice($clientDevices, $maintenance);

        $maintenance->load('clientDevices.device', 'clientDevices.client');

        return $this->makeResponseResource('Maintenance Created Successfully', new MaintenanceResource($maintenance));
    }
    public function show(Maintenance $maintenance, ShowMaintenanceAPIRequest $request): JsonResponse
    {
        $maintenance->load('clientDevices.device', 'clientDevices.client', 'technician.user');

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

    public function updateQuotation(Maintenance $maintenance, UpdateQuotationAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->updateQuotation($input, $maintenance);

        return $this->makeResponseResource('Maintenance price updated Successfully', new MaintenanceResource($maintenance));
    }
}
