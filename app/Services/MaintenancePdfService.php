<?php

namespace App\Services;

use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceClientDevice;
use App\Utils\Resources\ResourceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaintenancePdfService
{
    public function __construct(
        private readonly ResourceService $resourceService
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function generateAndUpload(int $maintenanceId): array
    {
        $maintenance = Maintenance::with([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user',
            'photos.clientDevice',
            'sparePartSuggestions.clientDevice.device',
            'actionLogs.technician.user',
        ])->find($maintenanceId);

        if (!$maintenance) {
            throw new ModelNotFoundException("No se encontró el mantenimiento {$maintenanceId}.");
        }

        $maintenanceClientDevices = MaintenanceClientDevice::query()
            ->where('maintenance_id', $maintenance->id)
            ->with([
                'serviceReport',
                'clientDevice.client.user',
                'clientDevice.device',
            ])
            ->get();

        $logoBase64 = $this->getPublicImageBase64(public_path('logo-c.png'));
        $signatureBase64 = $this->getS3ImageBase64(
            $maintenance->getAttributes()['signature_photo'] ?? null
        );

        $arrivalLog = $maintenance->actionLogs->where('action', 'on_the_way')->first()
            ?? $maintenance->actionLogs->where('action', 'start')->first();
        $startLog = $maintenance->actionLogs->where('action', 'start')->first();
        $endLog = $maintenance->actionLogs->where('action', 'end')->first();

        $generatedPdfs = [];

        foreach ($maintenanceClientDevices as $maintenanceClientDevice) {
            $serviceReport = $maintenanceClientDevice->serviceReport;
            $clientDevice = $maintenanceClientDevice->clientDevice;

            if (!$clientDevice) {
                Log::warning('No se encontró clientDevice para el PDF del mantenimiento.', [
                    'maintenance_id' => $maintenance->id,
                    'maintenance_client_device_id' => $maintenanceClientDevice->id,
                ]);
                continue;
            }

            if (!$serviceReport) {
                Log::warning('No se encontró serviceReport para el PDF del mantenimiento.', [
                    'maintenance_id' => $maintenance->id,
                    'maintenance_client_device_id' => $maintenanceClientDevice->id,
                    'client_device_id' => $clientDevice->id,
                ]);
                continue;
            }

            $devicePhotos = $maintenance->photos->where('client_device_id', $clientDevice->id);
            $deviceSparePartSuggestions = $maintenance->sparePartSuggestions->where('client_device_id', $clientDevice->id);

            $data = [
                'maintenance' => $maintenance,
                'maintenanceClientDevice' => $maintenanceClientDevice,
                'clientDevice' => $clientDevice,
                'client' => $clientDevice->client,
                'device' => $clientDevice->device,
                'technician' => $maintenance->technician,
                'sparePartSuggestions' => $deviceSparePartSuggestions,
                'photos' => $devicePhotos,
                'photosBase64' => $this->buildPhotosBase64($devicePhotos),
                'startLog' => $startLog,
                'endLog' => $endLog,
                'arrivalLog' => $arrivalLog,
                'serviceReport' => $serviceReport,
                'logoBase64' => $logoBase64,
                'signatureBase64' => $signatureBase64,
            ];

            $pdfContent = Pdf::loadView('pdfs.maintenance-service-order', $data)->output();
            $pdfFilename = sprintf(
                'maintenance_%d_device_%d_%d.pdf',
                $maintenance->id,
                $clientDevice->id,
                time()
            );

            $result = $this->resourceService->uploadFile($pdfFilename, $pdfContent, true);

            if (!($result['status'] ?? false)) {
                throw new \RuntimeException(
                    'Error al subir PDF a S3: ' . ($result['message'] ?? 'Unknown error')
                );
            }

            $pdfNameWithoutExt = pathinfo($pdfFilename, PATHINFO_FILENAME);
            $maintenanceClientDevice->update(['pdf_url' => $pdfNameWithoutExt]);
            $generatedPdfs[] = $pdfNameWithoutExt;
        }

        Log::info('PDFs generados para mantenimiento.', [
            'maintenance_id' => $maintenance->id,
            'pdfs' => $generatedPdfs,
            'total' => count($generatedPdfs),
        ]);

        return $generatedPdfs;
    }

    private function getPublicImageBase64(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $content = @file_get_contents($path);

        return is_string($content) ? $this->buildBase64ImageData($content) : null;
    }

    private function getS3ImageBase64(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        $content = $this->getS3FileContent('images/' . $fileName . '.PNG');

        return $content ? $this->buildBase64ImageData($content) : null;
    }

    /**
     * @param \Illuminate\Support\Collection<int, mixed> $photos
     * @return array<int, array<string, mixed>>
     */
    private function buildPhotosBase64($photos): array
    {
        $photosBase64 = [];

        foreach ($photos as $photo) {
            $photoName = $photo->getAttributes()['photo'] ?? null;

            if (!$photoName) {
                continue;
            }

            try {
                $photoContent = $this->getS3FileContent('images/' . $photoName . '.PNG');

                if (!$photoContent) {
                    continue;
                }

                $photosBase64[] = [
                    'base64' => $this->prepareImageForPdf($photoContent),
                    'type' => $photo->photo_type ?? 'other',
                    'created_at' => $photo->created_at ?? null,
                ];
            } catch (\Throwable $e) {
                Log::warning('No se pudo cargar la foto para el PDF.', [
                    'photo' => $photoName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return array_values(array_filter($photosBase64, fn (array $photo): bool => !empty($photo['base64'])));
    }

    private function getS3FileContent(string $path): ?string
    {
        try {
            return Storage::disk('s3')->get($path);
        } catch (\Throwable $e) {
            Log::warning('No se pudo obtener un archivo desde S3 para el PDF.', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function prepareImageForPdf(string $imageContent): ?string
    {
        if (!$this->canOptimizeImages()) {
            return $this->buildBase64ImageData($imageContent);
        }

        return $this->optimizeImageForPdf($imageContent) ?? $this->buildBase64ImageData($imageContent);
    }

    private function canOptimizeImages(): bool
    {
        return (bool) config('maintenance_pdf.optimize_images', false)
            && extension_loaded('gd')
            && function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled')
            && function_exists('imagejpeg');
    }

    private function optimizeImageForPdf(string $imageContent): ?string
    {
        try {
            $sourceImage = @\imagecreatefromstring($imageContent);

            if (!$sourceImage) {
                return null;
            }

            $originalWidth = \imagesx($sourceImage);
            $originalHeight = \imagesy($sourceImage);

            $maxWidth = max((int) config('maintenance_pdf.max_width', 800), 1);
            $maxHeight = max((int) config('maintenance_pdf.max_height', 600), 1);
            $jpegQuality = min(max((int) config('maintenance_pdf.jpeg_quality', 75), 1), 100);

            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1.0);
            $newWidth = max((int) ($originalWidth * $ratio), 1);
            $newHeight = max((int) ($originalHeight * $ratio), 1);

            $optimizedImage = \imagecreatetruecolor($newWidth, $newHeight);
            \imagealphablending($optimizedImage, false);
            \imagesavealpha($optimizedImage, true);
            $transparent = \imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
            \imagefill($optimizedImage, 0, 0, $transparent);

            \imagecopyresampled(
                $optimizedImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            ob_start();
            \imagejpeg($optimizedImage, null, $jpegQuality);
            $optimizedContent = ob_get_clean();

            \imagedestroy($sourceImage);
            \imagedestroy($optimizedImage);

            return is_string($optimizedContent)
                ? 'data:image/jpeg;base64,' . base64_encode($optimizedContent)
                : null;
        } catch (\Throwable $e) {
            Log::warning('Error al optimizar imagen para PDF.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildBase64ImageData(string $imageContent): ?string
    {
        $mimeType = $this->detectMimeType($imageContent) ?? 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
    }

    private function detectMimeType(string $content): ?string
    {
        if (!function_exists('finfo_open')) {
            return null;
        }

        $finfo = @finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return null;
        }

        $mimeType = @finfo_buffer($finfo, $content);
        finfo_close($finfo);

        return is_string($mimeType) && str_starts_with($mimeType, 'image/')
            ? $mimeType
            : null;
    }
}

