<?php

namespace App\Http\Controllers\Maintenance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\UpdatePaymentSupportAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use Illuminate\Http\JsonResponse;

class MaintenancePaymentSupportAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    /**
     * Actualizar soporte de pago de un mantenimiento
     *
     * @param Maintenance $maintenance
     * @param UpdatePaymentSupportAPIRequest $request
     * @return JsonResponse
     */
    public function __invoke(Maintenance $maintenance, UpdatePaymentSupportAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $fileName = $input['payment_support'];

        $maintenance = $this->maintenanceRepository->updatePaymentSupport($maintenance, $fileName);

        $maintenance->load(['clientDevices.device', 'technician.user', 'clientDevices.client']);

        return $this->makeResponseResource('Soporte de pago actualizado exitosamente', new MaintenanceResource($maintenance));
    }
}

