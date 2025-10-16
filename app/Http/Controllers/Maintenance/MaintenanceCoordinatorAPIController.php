<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CreateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\DeleteMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\ReadMaintenanceCoordinatorAPIRequest;
use App\Http\Requests\Maintenance\ShowMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UpdateQuotationAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\Maintenance\ClientMaintenancesCriteria;
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

}
