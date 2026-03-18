<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientDevice\GetLinkDeviceMaintenancesAPIRequest;
use App\Http\Requests\Maintenance\CreateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\DeleteMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\GenerateMaintenancePDFAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ShowMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateQuotationAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Jobs\GenerateMaintenancePDFJob;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceClientDevice;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\ClientDeviceMaintenancesCriteria;
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


    /**
     * Obtener el historial de mantenimientos de un equipo vinculado (linkDevice)
     *
     * @param ClientDevice $linkDevice
     * @param GetLinkDeviceMaintenancesAPIRequest $request
     * @return JsonResponse
     */
    public function getLinkDeviceMaintenances(ClientDevice $linkDevice, GetLinkDeviceMaintenancesAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new ClientDeviceMaintenancesCriteria($linkDevice->id));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at', 'desc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(8);

        $maintenances->load([
            'clientDevices.device',
            'clientDevices.client',
            'technician.user'
        ]);

        return $this->makeResponseResource(
            'Maintenances retrieved successfully',
            MaintenanceResource::collection($maintenances)
        );
    }

    public function generatePdf(Maintenance $maintenance, GenerateMaintenancePDFAPIRequest $request): JsonResponse
    {
        $devicesWithReport = MaintenanceClientDevice::query()
            ->where('maintenance_id', $maintenance->id)
            ->whereHas('serviceReport')
            ->count();

        if ($devicesWithReport === 0) {
            return response()->json([
                'success' => false,
                'message' => 'El mantenimiento no tiene reportes de servicio para generar el PDF.',
            ], 422);
        }

        $maintenance->load([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
        ]);

        GenerateMaintenancePDFJob::dispatch($maintenance)
            ->onQueue('PDFQueue_local');

        return response()->json([
            'success' => true,
            'data' => [
                'maintenance_id' => $maintenance->id,
                    'queue' => 'PDFQueue_local',
                'devices_with_report' => $devicesWithReport,
            ],
            'message' => 'La generación del PDF fue encolada correctamente.',
        ], 202);
    }
}
