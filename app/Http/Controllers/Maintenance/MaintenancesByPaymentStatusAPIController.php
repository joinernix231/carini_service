<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ReadMaintenancesByPaymentStatusAPIRequest;
use App\Http\Requests\Maintenance\UpdatePaymentSupportAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use App\Utils\Criterias\BasicCriteria\WhereNullOrValueCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaintenancesByPaymentStatusAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function __invoke(Maintenance $maintenance, ReadMaintenancesByPaymentStatusAPIRequest $request): JsonResponse
    {
        $this->maintenanceRepository->pushCriteria(new WhereFieldCriteria('status', 'quoted'));
        $this->maintenanceRepository->pushCriteria(new OrderByCriteria('created_at','asc'));

        $maintenances = $request->has('unpaginated') ?
            $this->maintenanceRepository->all() :
            $this->maintenanceRepository->paginate(20);
        $maintenance->load('clientDevices.device', 'clientDevices.client');

        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceResource::collection($maintenances));
    }
}
