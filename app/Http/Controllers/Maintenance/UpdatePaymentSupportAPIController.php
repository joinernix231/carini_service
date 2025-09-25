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
        $paymentSupportFile = $request->file('payment_support');
        
        // Generar nombre único para el archivo
        $fileName = 'payment_support_' . $maintenance->id . '_' . time() . '_' . Str::random(10) . '.' . $paymentSupportFile->getClientOriginalExtension();
        
        // Subir archivo a S3
        $filePath = $paymentSupportFile->storeAs('docs/pdfs', $fileName, 's3');
        
        // Actualizar el mantenimiento usando el repositorio
        $maintenance = $this->maintenanceRepository->updatePaymentSupport($maintenance, $fileName);
        
        // Cargar relaciones para la respuesta
        $maintenance->load(['clientDevice.device', 'technician.user', 'client']);
        
        return $this->makeResponseResource(
            'Soporte de pago actualizado exitosamente',
            new MaintenanceResource($maintenance)
        );
    }
}
