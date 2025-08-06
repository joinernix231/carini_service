<?php

namespace App\Jobs\Maintenance;

use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\Technician\TechnicianRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssignTechnicianJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $maintenanceId;

    public function __construct(int $maintenanceId)
    {
        $this->maintenanceId = $maintenanceId;
    }

    public function handle(TechnicianRepository $technicianRepository, MaintenanceRepository $maintenanceRepository): void
    {
        $maintenance = $maintenanceRepository->findWithoutFail($this->maintenanceId);

        $available = $technicianRepository->getAvailableTechnicianForShift($maintenance->date_maintenance);

        if (!$available) {
            logger()->warning("No hay técnicos disponibles para el mantenimiento {$maintenance->id}");
            return;
        }

        $technician = $available['technician'];
        $shift = $available['shift'];

        $maintenanceRepository->assignTechnician($maintenance, $technician, $shift);
    }
}

