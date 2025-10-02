<?php

namespace App\Console\Commands;

use App\Models\Maintenance\Maintenance;
use App\Models\User;
use App\Notifications\MantenimientoCreado;
use Illuminate\Console\Command;

class TestCoordinatorNotifications extends Command
{
    protected $signature = 'notifications:test-coordinators {--maintenance-id=}';
    protected $description = 'Probar notificaciones para coordinadores';

    public function handle()
    {
        $this->info('🧪 Probando notificaciones para coordinadores...');

        $maintenanceId = $this->option('maintenance-id');
        
        if ($maintenanceId) {
            $maintenance = Maintenance::find($maintenanceId);
            if (!$maintenance) {
                $this->error("❌ Mantenimiento con ID {$maintenanceId} no encontrado");
                return;
            }
        } else {
            $maintenance = Maintenance::first();
            if (!$maintenance) {
                $this->error("❌ No se encontró ningún mantenimiento en la base de datos");
                return;
            }
        }

        $this->info("🔧 Mantenimiento seleccionado: ID {$maintenance->id}");

        // Obtener coordinadores
        $coordinators = User::where('role', 'coordinador')->get();
        
        if ($coordinators->isEmpty()) {
            $this->error("❌ No se encontraron coordinadores en la base de datos");
            return;
        }

        $this->info("👥 Coordinadores encontrados: " . $coordinators->count());

        // Enviar notificación a cada coordinador
        foreach ($coordinators as $coordinator) {
            try {
                $coordinator->notify(new MantenimientoCreado($maintenance));
                $this->info("✅ Notificación enviada a: {$coordinator->name} ({$coordinator->email})");
            } catch (\Exception $e) {
                $this->error("❌ Error enviando notificación a {$coordinator->name}: " . $e->getMessage());
            }
        }

        $this->info('🎉 Pruebas completadas');
    }
}