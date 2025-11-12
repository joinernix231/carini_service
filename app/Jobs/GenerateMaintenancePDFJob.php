<?php

namespace App\Jobs;

use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Utils\Resources\ResourceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateMaintenancePDFJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Maintenance $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function handle(
        MaintenanceRepository $maintenanceRepository,
        ResourceService $resourceService
    ): void {
        try {
            // Cargar relaciones si no están cargadas
            if (!$this->maintenance->relationLoaded('clientDevices')) {
                $this->maintenance->load([
                    'clientDevices.client.user',
                    'clientDevices.device',
                    'technician.user',
                    'photos.clientDevice',
                    'sparePartSuggestions.clientDevice.device',
                    'actionLogs.technician.user',
                    'serviceReport'
                ]);
            } elseif (!$this->maintenance->relationLoaded('serviceReport')) {
                $this->maintenance->load('serviceReport');
            }

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
            if ($this->maintenance->signature_photo) {
                try {
                    // Obtener el nombre del archivo sin la URL
                    $signatureName = $this->maintenance->getAttributes()['signature_photo'] ?? null;
                    if ($signatureName) {
                        $s3Path = 'images/' . $signatureName . '.PNG';
                        if (Storage::disk('s3')->exists($s3Path)) {
                            $signatureContent = Storage::disk('s3')->get($s3Path);
                            $signatureBase64 = 'data:image/png;base64,' . base64_encode($signatureContent);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("No se pudo cargar la firma del cliente para el PDF: " . $e->getMessage());
                }
            }

            // Preparar datos para la vista del PDF
            $data = [
                'maintenance' => $this->maintenance,
                'client' => $this->maintenance->client,
                'devices' => $this->maintenance->clientDevices,
                'technician' => $this->maintenance->technician,
                'spareParts' => $this->maintenance->sparePartSuggestions,
                'photos' => $this->maintenance->photos,
                'startLog' => $this->maintenance->actionLogs->where('action', 'start')->first(),
                'endLog' => $this->maintenance->actionLogs->where('action', 'end')->first(),
                'serviceReport' => $this->maintenance->serviceReport,
                'logoBase64' => $logoBase64,
                'signatureBase64' => $signatureBase64,
            ];

            // Generar el PDF usando DomPDF
            $pdf = Pdf::loadView('pdfs.maintenance-service-order', $data);
            $pdfContent = $pdf->output();


            $pdfFilename = 'maintenance_' . $this->maintenance->id . '_' . time() . '.pdf';

            $result = $resourceService->uploadFile($pdfFilename, $pdfContent, true);

            if (!$result['status']) {
                throw new \Exception('Error al subir PDF a S3: ' . ($result['message'] ?? 'Unknown error'));
            }


            $pdfNameWithoutExt = pathinfo($pdfFilename, PATHINFO_FILENAME);

            $maintenanceRepository->update(
                ['pdf_url' => $pdfNameWithoutExt],
                $this->maintenance->id
            );

            Log::info("PDF generado exitosamente para mantenimiento ID: {$this->maintenance->id}", [
                'pdf_filename' => $pdfFilename,
                'pdf_name_saved' => $pdfNameWithoutExt
            ]);

        } catch (\Exception $e) {
            Log::error("Error al generar PDF para mantenimiento ID: {$this->maintenance->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-lanzar la excepción para que el job falle y se pueda reintentar
            throw $e;
        }
    }
}
