<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MaintenancePDFPreviewController extends Controller
{
    /**
     * Previsualizar el PDF de una orden de servicio
     * Ruta sin middleware para poder acceder fácilmente
     *
     * @param int $id ID del mantenimiento
     * @return Response
     */
    public function preview(int $id): Response
    {
        // Buscar el mantenimiento y cargar todas las relaciones necesarias
        $maintenance = Maintenance::with([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
            'serviceReport'
        ])->findOrFail($id);

        // Preparar datos para la vista del PDF
        $data = $this->preparePdfData($maintenance);

        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.maintenance-service-order', $data);

        // Retornar el PDF para visualización en el navegador
        return $pdf->stream('orden_servicio_' . $maintenance->id . '.pdf');
    }

    /**
     * Descargar el PDF de una orden de servicio
     * Ruta sin middleware para poder acceder fácilmente
     *
     * @param int $id ID del mantenimiento
     * @return Response
     */
    public function download(int $id): Response
    {
        // Buscar el mantenimiento y cargar todas las relaciones necesarias
        $maintenance = Maintenance::with([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
            'serviceReport'
        ])->findOrFail($id);

        // Preparar datos para la vista del PDF
        $data = $this->preparePdfData($maintenance);

        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.maintenance-service-order', $data);

        // Retornar el PDF para descarga
        return $pdf->download('orden_servicio_' . $maintenance->id . '.pdf');
    }

    /**
     * Preparar datos e imágenes para el PDF
     */
    private function preparePdfData(Maintenance $maintenance): array
    {
        // Preparar imágenes para el PDF (DomPDF necesita base64 o rutas locales)
        $logoBase64 = null;
        $signatureBase64 = null;
        
        // Logo desde public
        $logoPath = public_path('logo-c.png');
        if (file_exists($logoPath)) {
            $logoContent = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        // Firma del cliente desde S3
        if ($maintenance->signature_photo) {
            try {
                $signatureName = $maintenance->getAttributes()['signature_photo'] ?? null;
                if ($signatureName) {
                    $s3Path = 'images/' . $signatureName . '.PNG';
                    if (Storage::disk('s3')->exists($s3Path)) {
                        $signatureContent = Storage::disk('s3')->get($s3Path);
                        $signatureBase64 = 'data:image/png;base64,' . base64_encode($signatureContent);
                    }
                }
            } catch (\Exception $e) {
                // Silenciar error para preview
            }
        }

        return [
            'maintenance' => $maintenance,
            'client' => $maintenance->client,
            'devices' => $maintenance->clientDevices,
            'technician' => $maintenance->technician,
            'spareParts' => $maintenance->sparePartSuggestions,
            'photos' => $maintenance->photos,
            'startLog' => $maintenance->actionLogs->where('action', 'start')->first(),
            'endLog' => $maintenance->actionLogs->where('action', 'end')->first(),
            'serviceReport' => $maintenance->serviceReport,
            'logoBase64' => $logoBase64,
            'signatureBase64' => $signatureBase64,
        ];
    }
}

