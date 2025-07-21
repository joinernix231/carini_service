<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceType\ReadMaintenanceTypeAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceTypeResource;
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


        return $this->makeResponseResource('Maintenances retrieved Successfully', MaintenanceTypeResource::collection($maintenanceType));

    }

}
