<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\UpdatePaymentSupportAPIRequest;
use App\Http\Resources\Maintenance\MaintenanceResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdatePaymentSupportAPIController extends Controller
{
    private MaintenanceRepository $maintenanceRepository;

    public function __construct(MaintenanceRepository $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function __invoke(Maintenance $maintenance, UpdatePaymentSupportAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $fileName = $input['payment_support'];

        $maintenance = $this->maintenanceRepository->updatePaymentSupport($maintenance, $fileName);

        $maintenance->load(['clientDevices.device', 'technician.user', 'clientDevices.client']);

        return $this->makeResponseResource('Soporte de pago actualizado exitosamente', new MaintenanceResource($maintenance)
        );
    }
}
