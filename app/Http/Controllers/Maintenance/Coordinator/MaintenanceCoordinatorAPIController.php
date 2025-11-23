<?php

namespace App\Http\Controllers\Maintenance\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ReadMaintenanceCoordinatorAPIRequest;
use App\Http\Requests\Maintenance\ReadUnconfirmedMaintenancesAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use App\Utils\Criterias\BasicCriteria\WhereNullCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceCoordinatorAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function index(ReadMaintenanceCoordinatorAPIRequest $request)
    {
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at','asc'));

        if ($request->has('filters'))
            $this->maintenanceRepository->pushCriteria(new FiltersCriteria($request->get('filters')));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $maintenances->load(['clientDevices.device', 'clientDevices.client']);


        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));

    }

    /**
     * Obtener mantenimientos sin confirmar por el cliente
     */
    public function unconfirmed(ReadUnconfirmedMaintenancesAPIRequest $request): JsonResponse
    {
        // Aplicar filtros de estado y confirmación usando criterios específicos
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'assigned'));
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('confirmation_required', true));
        $this->maintenanceRepository->pushCriteria(new WhereNullCriteria('confirmed_at'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('confirmation_deadline', 'asc'));

        // Aplicar filtros adicionales si están disponibles
        if ($request->has('filters')) {
            $this->maintenanceRepository->pushCriteria(new FiltersCriteria($request->get('filters')));
        }

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);

        $maintenances->load([
            'clientDevices.client',
            'clientDevices.device',
            'technician.user'
        ]);

        return $this->makeResponseResource(
            'Unconfirmed maintenances retrieved successfully',
            MaintenanceResource::collection($maintenances)
        );
    }

}
