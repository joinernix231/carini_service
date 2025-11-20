<?php

namespace App\Jobs;

use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceClientDevice;
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
            // Cargar relaciones necesarias
            if (!$this->maintenance->relationLoaded('clientDevices')) {
                $this->maintenance->load([
                    'clientDevices.client.user',
                    'clientDevices.device',
                    'technician.user',
                    'photos.clientDevice',
                    'sparePartSuggestions.clientDevice.device',
                    'actionLogs.technician.user',
                ]);
            }

            // Preparar logo base64 (se usa para todos los PDFs)
            $logoBase64 = null;
            $logoPath = public_path('logo-c.png');
            if (file_exists($logoPath)) {
                $logoContent = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
            }

            // Preparar logs de acción (compartidos para todos los dispositivos)
            $arrivalLog = $this->maintenance->actionLogs->where('action', 'on_the_way')->first() 
                ?? $this->maintenance->actionLogs->where('action', 'start')->first();
            $startLog = $this->maintenance->actionLogs->where('action', 'start')->first();
            $endLog = $this->maintenance->actionLogs->where('action', 'end')->first();

            // Generar PDF para cada dispositivo del mantenimiento
            $generatedPdfs = [];
            
            foreach ($this->maintenance->clientDevices as $clientDevice) {
                // Obtener el pivot (MaintenanceClientDevice)
                $maintenanceClientDevice = MaintenanceClientDevice::where('maintenance_id', $this->maintenance->id)
                    ->where('client_device_id', $clientDevice->id)
                    ->with(['serviceReport'])
                    ->first();

                if (!$maintenanceClientDevice) {
                    Log::warning("No se encontró MaintenanceClientDevice para maintenance_id: {$this->maintenance->id}, client_device_id: {$clientDevice->id}");
                    continue;
                }

                $serviceReport = $maintenanceClientDevice->serviceReport;
                
                if (!$serviceReport) {
                    Log::warning("No se encontró ServiceReport para maintenance_client_device_id: {$maintenanceClientDevice->id}");
                    continue;
                }

                // Preparar firma del cliente desde S3
                $signatureBase64 = null;
                if ($this->maintenance->signature_photo) {
                    try {
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

                // Filtrar fotos y repuestos sugeridos para este dispositivo específico
                $devicePhotos = $this->maintenance->photos->where('client_device_id', $clientDevice->id);
                $deviceSparePartSuggestions = $this->maintenance->sparePartSuggestions->where('client_device_id', $clientDevice->id);

                // Preparar datos para la vista del PDF
                $data = [
                    'maintenance' => $this->maintenance,
                    'maintenanceClientDevice' => $maintenanceClientDevice,
                    'clientDevice' => $clientDevice,
                    'client' => $clientDevice->client,
                    'device' => $clientDevice->device,
                    'technician' => $this->maintenance->technician,
                    'sparePartSuggestions' => $deviceSparePartSuggestions,
                    'photos' => $devicePhotos,
                    'startLog' => $startLog,
                    'endLog' => $endLog,
                    'arrivalLog' => $arrivalLog,
                    'serviceReport' => $serviceReport,
                    'logoBase64' => $logoBase64,
                    'signatureBase64' => $signatureBase64,
                ];

                // Generar el PDF usando DomPDF
                $pdf = Pdf::loadView('pdfs.maintenance-service-order', $data);
                $pdfContent = $pdf->output();

                // Nombre del archivo incluyendo el ID del dispositivo
                $pdfFilename = 'maintenance_' . $this->maintenance->id . '_device_' . $clientDevice->id . '_' . time() . '.pdf';

                $result = $resourceService->uploadFile($pdfFilename, $pdfContent, true);

                if (!$result['status']) {
                    throw new \Exception('Error al subir PDF a S3: ' . ($result['message'] ?? 'Unknown error'));
                }

                $pdfNameWithoutExt = pathinfo($pdfFilename, PATHINFO_FILENAME);
                $generatedPdfs[] = $pdfNameWithoutExt;

                // Guardar el pdf_url en maintenance_client_device
                $maintenanceClientDevice->update(['pdf_url' => $pdfNameWithoutExt]);

                Log::info("PDF generado exitosamente para mantenimiento ID: {$this->maintenance->id}, dispositivo ID: {$clientDevice->id}", [
                    'pdf_filename' => $pdfFilename,
                    'pdf_name_saved' => $pdfNameWithoutExt
                ]);
            }

            // Guardar los nombres de los PDFs generados (puedes guardarlos en JSON en maintenance o en otra tabla)
            // Por ahora solo logueamos
            Log::info("PDFs generados para mantenimiento ID: {$this->maintenance->id}", [
                'pdfs' => $generatedPdfs,
                'total' => count($generatedPdfs)
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
