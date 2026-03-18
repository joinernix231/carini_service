<?php

namespace App\Jobs;

use App\Services\MaintenancePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateMaintenancePDFJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    /**
     * @param int $maintenanceId
     */
    public function __construct(public readonly int $maintenanceId)
    {
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(MaintenancePdfService $maintenancePdfService): void
    {
        try {
            $maintenancePdfService->generateAndUpload($this->maintenanceId);
        } catch (\Throwable $e) {
            Log::error("Error al generar PDF para mantenimiento ID: {$this->maintenanceId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
