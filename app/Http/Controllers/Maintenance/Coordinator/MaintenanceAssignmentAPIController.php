<?php

namespace App\Http\Controllers\Maintenance\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\AssignMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\UploadPriceMaintenanceAPIRequest;
use App\Http\Requests\Maintenance\VerifyPaymentMaintenanceAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use Illuminate\Http\JsonResponse;

class MaintenanceAssignmentAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    /**
     * Asignar mantenimiento a un técnico
     *
     * @param Maintenance $maintenance
     * @param AssignMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function assignMaintenance(Maintenance $maintenance, AssignMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->assignMaintenance($maintenance, $input);
        $maintenance->load(['clientDevices', 'technician']);

        return $this->makeResponseResource('Maintenance assigned Successfully', new MaintenanceResource($maintenance));
    }

    /**
     * Verificar pago de un mantenimiento
     *
     * @param Maintenance $maintenance
     * @param VerifyPaymentMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function verifyPaymentOfMaintenance(Maintenance $maintenance, VerifyPaymentMaintenanceAPIRequest $request): JsonResponse
    {
        $maintenance = $this->maintenanceRepository->verifyPayment($maintenance);

        return $this->makeResponseResource('Maintenance verify Successfully', new MaintenanceResource($maintenance));
    }

    /**
     * Subir precio de un mantenimiento
     *
     * @param Maintenance $maintenance
     * @param UploadPriceMaintenanceAPIRequest $request
     * @return JsonResponse
     */
    public function uploadPrice(Maintenance $maintenance, UploadPriceMaintenanceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $maintenance = $this->maintenanceRepository->uploadPrice($input, $maintenance);

        return $this->makeResponseResource('Maintenance price Successfully', new MaintenanceResource($maintenance));
    }
}

