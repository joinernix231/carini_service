<?php

namespace App\Http\Controllers\Maintenance\Queries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ReadMaintenanceNotAssignedAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use App\Utils\Criterias\BasicCriteria\WhereNullCriteria;
use App\Utils\Criterias\BasicCriteria\WhereNullOrValueCriteria;
use Illuminate\Http\JsonResponse;

class MaintenanceNotAssignedListAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function __invoke(ReadMaintenanceNotAssignedAPIRequest $request): JsonResponse
    {
        // Listos para asignar: cotizados, sin técnico, y con el pago aprobado o sin cobro.
        // Antes esta lista pedía status pending, así que un servicio ya cotizado no aparecía.
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'quoted'));
        $this->maintenanceRepository->pushCriteria(new WhereNullCriteria('technician_id'));
        $this->maintenanceRepository->pushCriteria(new WhereNullOrValueCriteria('is_paid', true));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at','asc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        
        $maintenances->load([
            'clientDevices.client',
            'clientDevices.device',
        ]);

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));
    }
}

