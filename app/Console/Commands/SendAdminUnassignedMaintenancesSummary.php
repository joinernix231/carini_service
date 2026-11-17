<?php

namespace App\Console\Commands;

use App\Notifications\Admin\AdminResumenMantenimientosSinAsignar;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdminUnassignedMaintenancesSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:summary-unassigned-maintenances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía resumen semanal de mantenimientos sin asignar a los administradores';

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
        $this->info('Generando resumen semanal de mantenimientos sin asignar...');

        // Obtener mantenimientos sin asignar (pendientes)
        $unassignedMaintenances = $this->maintenanceRepository->model()
            ->where('status', 'pending')
            ->with([
                'clientDevices.client',
                'clientDevices.device'
            ])
            ->get();

        $count = $unassignedMaintenances->count();

        $this->info("Se encontraron {$count} mantenimiento(s) sin asignar.");

        // Preparar datos de mantenimientos para la notificación
        $maintenancesData = $unassignedMaintenances->map(function ($maintenance) {
            $firstDevice = $maintenance->clientDevices->first();
            $clientName = $firstDevice && $firstDevice->client ? $firstDevice->client->name : 'Cliente';
            
            return [
                'id' => $maintenance->id,
                'client_name' => $clientName,
                'created_at' => $maintenance->created_at->format('Y-m-d H:i:s'),
                'days_since_created' => $maintenance->created_at->diffInDays(now()),
            ];
        })->toArray();

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
                $administrator->notify(new AdminResumenMantenimientosSinAsignar($count, $maintenancesData));
                $notifiedCount++;
            } catch (\Exception $e) {
                Log::error('Error enviando resumen de mantenimientos sin asignar al administrador', [
                    'administrator_id' => $administrator->id,
                    'count' => $count,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Se enviaron {$notifiedCount} notificación(es) a administradores.");

        return Command::SUCCESS;
    }
}

