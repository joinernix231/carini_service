<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAdminMaintenanceAPIRequest;
use App\Http\Requests\Admin\DeleteAdminMaintenanceAPIRequest;
use App\Http\Requests\Admin\GetClientDeviceMaintenancesAPIRequest;
use App\Http\Requests\Admin\GetMaintenancePhotosAPIRequest;
use App\Http\Requests\Admin\ReadAdminMaintenanceAPIRequest;
use App\Http\Requests\Admin\ShowAdminMaintenanceAPIRequest;
use App\Http\Requests\Admin\UpdateAdminMaintenanceAPIRequest;
use App\Http\Resources\Maintenance\MaintenancePhotoResource;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenancePhotoRepository;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\ClientDeviceMaintenancesCriteria;
use App\Utils\Criterias\Maintenance\MaintenancePhotosCriteria;
use Illuminate\Http\JsonResponse;

class AdminMaintenanceAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;
    private MaintenancePhotoRepository $maintenancePhotoRepository;

    public function __construct(
        MaintenanceRepository $maintenanceRepository,
        MaintenancePhotoRepository $maintenancePhotoRepository
    ) {
        $this->maintenanceRepository = $maintenanceRepository;
        $this->maintenancePhotoRepository = $maintenancePhotoRepository;
    }

    /**
     * Listar todos los mantenimientos (Admin)
     *
     * @param ReadAdminMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function index(ReadAdminMaintenanceAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at', 'desc'));

        if ($request->has('filters')) {
            $this->maintenanceRepository->pushCriteria(new FiltersCriteria($request->get('filters')));
        }

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);

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

    /**
     * Crear un nuevo mantenimiento (Admin)
     *
     * @param CreateAdminMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function store(CreateAdminMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();
        $clientDevices = $input['client_devices'];

        $maintenance = $this->maintenanceRepository->create($input);
        $this->maintenanceRepository->AssociateMaintenanceDevice($clientDevices, $maintenance);

        $maintenance->load('clientDevices.device', 'clientDevices.client');

        return $this->makeResponseResource(
            'Maintenance created successfully',
            new MaintenanceResource($maintenance)
        );
    }

    /**
     * Mostrar un mantenimiento específico (Admin)
     *
     * @param Maintenance $maintenance
     * @param ShowAdminMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function show(Maintenance $maintenance, ShowAdminMaintenanceAPIRequest $request): JsonResponse
    {
        $maintenance->load([
            'clientDevices.device',
            'clientDevices.client',
            'technician.user'
        ]);

        return $this->makeResponseResource(
            'Maintenance retrieved successfully',
            new MaintenanceResource($maintenance)
        );
    }

    /**
     * Actualizar un mantenimiento (Admin)
     *
     * @param Maintenance $maintenance
     * @param UpdateAdminMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function update(Maintenance $maintenance, UpdateAdminMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->update($input, $maintenance->id);

        $maintenance->load([
            'clientDevices.device',
            'clientDevices.client',
            'technician.user'
        ]);

        return $this->makeResponseResource(
            'Maintenance updated successfully',
            new MaintenanceResource($maintenance)
        );
    }

    /**
     * Eliminar un mantenimiento (Admin)
     *
     * @param Maintenance $maintenance
     * @param DeleteAdminMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function destroy(Maintenance $maintenance, DeleteAdminMaintenanceAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->delete($maintenance->id);

        return $this->makeResponse('Maintenance deleted successfully', [$maintenance->id]);
    }

    /**
     * Obtener todos los mantenimientos de un clientDevice específico
     *
     * @param ClientDevice $clientDevice
     * @param GetClientDeviceMaintenancesAPIRequest $request
     * @return JsonResponse
     */
    public function getClientDeviceMaintenances(ClientDevice $clientDevice, GetClientDeviceMaintenancesAPIRequest $request): JsonResponse {
        $this->maintenanceRepository->pushCriteria(new ClientDeviceMaintenancesCriteria($clientDevice->id));

        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at', 'desc'));

        if ($request->has('filters')) {
            $this->maintenanceRepository->pushCriteria(new FiltersCriteria($request->get('filters')));
        }

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

    /**
     * Obtener fotos de un mantenimiento filtradas por tipo y opcionalmente por dispositivo
     *
     * @param Maintenance $maintenance
     * @param GetMaintenancePhotosAPIRequest $request
     * @return JsonResponse
     */
    public function getMaintenancePhotos(Maintenance $maintenance, GetMaintenancePhotosAPIRequest $request): JsonResponse
    {
        $this->maintenancePhotoRepository->pushCriteria(new MaintenancePhotosCriteria($maintenance->id));

        $this->maintenancePhotoRepository->pushCriteria(new OrderByCriteria('created_at', 'desc'));

        // Construir filtros desde parámetros directos o usar el parámetro filters
        $filters = [];
        if ($request->has('photo_type')) {
            $filters[] = 'photo_type|is|' . $request->input('photo_type');
        }
        if ($request->has('device_id')) {
            $filters[] = 'client_device_id|is|' . $request->input('device_id');
        }

        // Si hay filtros construidos o si viene el parámetro filters, aplicarlos
        if (!empty($filters)) {
            $this->maintenancePhotoRepository->pushCriteria(new FiltersCriteria(implode(';', $filters)));
        } elseif ($request->has('filters')) {
            $this->maintenancePhotoRepository->pushCriteria(new FiltersCriteria($request->get('filters')));
        }

        $photos = $request->has('unpaginated') ?
            $this->maintenancePhotoRepository->all() :
            $this->maintenancePhotoRepository->paginate(20);

        $photos->load(['clientDevice.device']);

        return $this->makeResponseResource(
            'Maintenance photos retrieved successfully',
            MaintenancePhotoResource::collection($photos)
        );
    }
}

