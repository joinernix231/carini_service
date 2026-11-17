<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceClientDevice;
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
    public function preview(int $id, ?int $deviceId = null): Response
    {
        // Buscar el mantenimiento y cargar todas las relaciones necesarias
        $maintenance = Maintenance::with([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
        ])->findOrFail($id);

        // Si no se especifica dispositivo, usar el primero con reporte
        if (!$deviceId) {
            $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $id)
                ->whereHas('serviceReport')
                ->with(['serviceReport', 'clientDevice.device', 'clientDevice.client'])
                ->first();
        } else {
            $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $id)
                ->where('client_device_id', $deviceId)
                ->with(['serviceReport', 'clientDevice.device', 'clientDevice.client'])
                ->first();
        }

        if (!$maintenanceClientDevice || !$maintenanceClientDevice->serviceReport) {
            abort(404, 'No se encontró reporte de servicio para este dispositivo');
        }

        // Preparar datos para la vista del PDF
        $data = $this->preparePdfData($maintenance, $maintenanceClientDevice);

        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.maintenance-service-order', $data);

        // Retornar el PDF para visualización en el navegador
        return $pdf->stream('orden_servicio_' . $maintenance->id . '_device_' . $maintenanceClientDevice->clientDevice->id . '.pdf');
    }

    /**
     * Descargar el PDF de una orden de servicio
     * Ruta sin middleware para poder acceder fácilmente
     *
     * @param int $id ID del mantenimiento
     * @return Response
     */
    public function download(int $id, ?int $deviceId = null): Response
    {
        // Buscar el mantenimiento y cargar todas las relaciones necesarias
        $maintenance = Maintenance::with([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
        ])->findOrFail($id);

        // Si no se especifica dispositivo, usar el primero con reporte
        if (!$deviceId) {
            $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $id)
                ->whereHas('serviceReport')
                ->with(['serviceReport', 'clientDevice.device', 'clientDevice.client'])
                ->first();
        } else {
            $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $id)
                ->where('client_device_id', $deviceId)
                ->with(['serviceReport', 'clientDevice.device', 'clientDevice.client'])
                ->first();
        }

        if (!$maintenanceClientDevice || !$maintenanceClientDevice->serviceReport) {
            abort(404, 'No se encontró reporte de servicio para este dispositivo');
        }

        // Preparar datos para la vista del PDF
        $data = $this->preparePdfData($maintenance, $maintenanceClientDevice);

        // Generar el PDF
        $pdf = Pdf::loadView('pdfs.maintenance-service-order', $data);

        // Retornar el PDF para descarga
        return $pdf->download('orden_servicio_' . $maintenance->id . '_device_' . $maintenanceClientDevice->clientDevice->id . '.pdf');
    }

    /**
     * Preparar datos e imágenes para el PDF
     */
    private function preparePdfData(Maintenance $maintenance, MaintenanceClientDevice $maintenanceClientDevice): array
    {
        $clientDevice = $maintenanceClientDevice->clientDevice;
        $serviceReport = $maintenanceClientDevice->serviceReport;

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

        $arrivalLog = $maintenance->actionLogs->where('action', 'on_the_way')->first() 
            ?? $maintenance->actionLogs->where('action', 'start')->first();

        // Filtrar fotos y repuestos sugeridos para este dispositivo específico
        $devicePhotos = $maintenance->photos->where('client_device_id', $clientDevice->id);
        $deviceSparePartSuggestions = $maintenance->sparePartSuggestions->where('client_device_id', $clientDevice->id);

        // Preparar fotos en base64 para el PDF con información adicional (optimizadas)
        $photosBase64 = [];
        foreach ($devicePhotos as $photo) {
            try {
                $photoName = $photo->getAttributes()['photo'] ?? null;
                if ($photoName) {
                    $s3Path = 'images/' . $photoName . '.PNG';
                    if (Storage::disk('s3')->exists($s3Path)) {
                        $photoContent = Storage::disk('s3')->get($s3Path);
                        $optimizedBase64 = $this->optimizeImageForPdf($photoContent);
                        if ($optimizedBase64) {
                            $photosBase64[] = [
                                'base64' => $optimizedBase64,
                                'type' => $photo->photo_type ?? 'other',
                                'created_at' => $photo->created_at ?? null,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silenciar error para preview
            }
        }

        return [
            'maintenance' => $maintenance,
            'maintenanceClientDevice' => $maintenanceClientDevice,
            'clientDevice' => $clientDevice,
            'client' => $clientDevice->client,
            'device' => $clientDevice->device,
            'technician' => $maintenance->technician,
            'sparePartSuggestions' => $deviceSparePartSuggestions,
            'photos' => $devicePhotos,
            'photosBase64' => $photosBase64,
            'startLog' => $maintenance->actionLogs->where('action', 'start')->first(),
            'endLog' => $maintenance->actionLogs->where('action', 'end')->first(),
            'arrivalLog' => $arrivalLog,
            'serviceReport' => $serviceReport,
            'logoBase64' => $logoBase64,
            'signatureBase64' => $signatureBase64,
        ];
    }

    /**
     * Optimiza una imagen para incluirla en el PDF
     * Redimensiona a máximo 800px de ancho y convierte a JPEG con calidad 75%
     *
     * @param string $imageContent Contenido binario de la imagen
     * @return string|null Base64 de la imagen optimizada o null si falla
     */
    private function optimizeImageForPdf(string $imageContent): ?string
    {
        try {
            if (!function_exists('imagecreatefromstring')) {
                return $this->buildBase64ImageData($imageContent);
            }

            // Crear imagen desde el contenido
            $sourceImage = @\imagecreatefromstring($imageContent);
            if (!$sourceImage) {
                return $this->buildBase64ImageData($imageContent);
            }

            // Obtener dimensiones originales
            $originalWidth = \imagesx($sourceImage);
            $originalHeight = \imagesy($sourceImage);

            // Calcular nuevas dimensiones (máximo 800px de ancho, mantener proporción)
            $maxWidth = 800;
            $maxHeight = 600;
            
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1.0);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            // Crear nueva imagen redimensionada
            $optimizedImage = \imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia si es PNG
            \imagealphablending($optimizedImage, false);
            \imagesavealpha($optimizedImage, true);
            $transparent = \imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
            \imagefill($optimizedImage, 0, 0, $transparent);

            // Redimensionar con mejor calidad
            \imagecopyresampled(
                $optimizedImage,
                $sourceImage,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            // Capturar como JPEG en memoria (más pequeño que PNG)
            ob_start();
            \imagejpeg($optimizedImage, null, 75); // Calidad 75%
            $optimizedContent = ob_get_clean();

            // Limpiar recursos
            \imagedestroy($sourceImage);
            \imagedestroy($optimizedImage);

            // Retornar base64
            return 'data:image/jpeg;base64,' . base64_encode($optimizedContent);
        } catch (\Throwable $e) {
            return $this->buildBase64ImageData($imageContent);
        }
    }

    private function buildBase64ImageData(string $imageContent): ?string
    {
        $mimeType = function_exists('finfo_buffer')
            ? @finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $imageContent)
            : null;

        if (!is_string($mimeType) || strpos($mimeType, 'image/') !== 0) {
            $mimeType = 'image/png';
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
    }
}

