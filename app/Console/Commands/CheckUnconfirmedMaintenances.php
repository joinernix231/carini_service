<?php

namespace App\Console\Commands;

use App\Listeners\Maintenance\NotifyAdminMaintenanceUnconfirmedListener;
use App\Notifications\Maintenance\ClienteNoConfirmoMantenimiento;
use App\Repositories\Maintenance\MaintenanceRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckUnconfirmedMaintenances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenances:check-unconfirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica mantenimientos sin confirmar por el cliente y notifica a coordinadores';

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
        $this->info('Verificando mantenimientos sin confirmar...');

        // Usar el repositorio para obtener mantenimientos sin confirmar
        $unconfirmedMaintenances = $this->maintenanceRepository->getUnconfirmedMaintenancesRequiringNotification();

        if ($unconfirmedMaintenances->isEmpty()) {
            $this->info('No se encontraron mantenimientos sin confirmar que requieran notificación.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$unconfirmedMaintenances->count()} mantenimiento(s) sin confirmar.");

        // Obtener todos los coordinadores usando el repositorio
        $coordinators = $this->userRepository->findWhere([
            'role' => 'coordinador',
        ]);

        if ($coordinators->isEmpty()) {
            $this->warn('No se encontraron coordinadores para notificar.');
            return Command::SUCCESS;
        }

        $notifiedCount = 0;

        // Listener para notificar administradores
        $adminListener = new NotifyAdminMaintenanceUnconfirmedListener($this->userRepository);

        foreach ($unconfirmedMaintenances as $maintenance) {
            // Marcar como notificado usando el repositorio
            $this->maintenanceRepository->markCoordinatorNotified($maintenance);

            // Notificar a todos los coordinadores
            foreach ($coordinators as $coordinator) {
                try {
                    $coordinator->notify(new ClienteNoConfirmoMantenimiento($maintenance));
                    $notifiedCount++;
                } catch (\Exception $e) {
                    Log::error('Error al notificar coordinador sobre mantenimiento sin confirmar', [
                        'coordinator_id' => $coordinator->id,
                        'maintenance_id' => $maintenance->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Notificar a administradores
            try {
                $adminListener->handle($maintenance);
            } catch (\Exception $e) {
                Log::error('Error al notificar administrador sobre mantenimiento sin confirmar', [
                    'maintenance_id' => $maintenance->id,
                    'error' => $e->getMessage()
                ]);
            }

            $this->info("Mantenimiento #{$maintenance->id} notificado a coordinadores y administradores.");
        }

        $this->info("Se enviaron {$notifiedCount} notificación(es) a coordinadores.");

        return Command::SUCCESS;
    }
}
