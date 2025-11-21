<?php

namespace App\Console\Commands;

use App\Notifications\Admin\AdminResumenMantenimientosCreados;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdminMaintenancesCreatedSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:summary-maintenances-created {--period=diario : Periodo del resumen (diario o semanal)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía resumen de mantenimientos creados a los administradores';

    protected MaintenanceRepository $maintenanceRepository;
    protected UserRepository $userRepository;

    public function __construct(
        MaintenanceRepository $maintenanceRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->maintenanceRepository = $maintenanceRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $period = $this->option('period');
        
        if (!in_array($period, ['diario', 'semanal'])) {
            $this->error('El periodo debe ser "diario" o "semanal"');
            return Command::FAILURE;
        }

        $this->info("Generando resumen {$period} de mantenimientos creados...");

        // Calcular fechas según el periodo
        if ($period === 'diario') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        }

        // Contar mantenimientos creados en el periodo
        $count = $this->maintenanceRepository->model()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $this->info("Se encontraron {$count} mantenimiento(s) creado(s) en el periodo.");

        // Obtener todos los administradores
        $administrators = $this->userRepository->findWhere([
            'role' => 'administrador',
        ]);

        if ($administrators->isEmpty()) {
            $this->warn('No se encontraron administradores para notificar.');
            return Command::SUCCESS;
        }

        $notifiedCount = 0;

        foreach ($administrators as $administrator) {
            try {
                $administrator->notify(new AdminResumenMantenimientosCreados($count, $period));
                $notifiedCount++;
            } catch (\Exception $e) {
                Log::error('Error enviando resumen de mantenimientos creados al administrador', [
                    'administrator_id' => $administrator->id,
                    'period' => $period,
                    'count' => $count,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Se enviaron {$notifiedCount} notificación(es) a administradores.");

        return Command::SUCCESS;
    }
}

