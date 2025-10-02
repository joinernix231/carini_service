<?php

namespace App\Console\Commands;

use App\Models\Maintenance\Maintenance;
use App\Models\User;
use App\Notifications\MantenimientoAsignado;
use App\Notifications\MantenimientoCompletado;
use App\Notifications\MaintenanceCreated;
use App\Services\ExpoPushService;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    protected $signature = 'notifications:test {--user-id=} {--maintenance-id=} {--type=all}';
    protected $description = 'Probar el sistema de notificaciones push';

    public function handle()
    {
        $this->info('🧪 Iniciando pruebas del sistema de notificaciones...');

        $userId = $this->option('user-id');
        $maintenanceId = $this->option('maintenance-id');
        $type = $this->option('type');

        // Obtener usuario
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("❌ Usuario con ID {$userId} no encontrado");
                return;
            }
        } else {
            $user = User::where('role', 'tecnico')->first();
            if (!$user) {
                $this->error("❌ No se encontró ningún técnico en la base de datos");
                return;
            }
        }

        $this->info("👤 Usuario seleccionado: {$user->name} ({$user->role})");

        // Obtener mantenimiento
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

        // Probar según el tipo
        switch ($type) {
            case 'assigned':
                $this->testMantenimientoAsignado($user, $maintenance);
                break;
            case 'completed':
                $this->testMantenimientoCompletado($user, $maintenance);
                break;
            case 'urgent':
                $this->testAlertaUrgente($user, $maintenance);
                break;
            case 'all':
            default:
                $this->testMantenimientoAsignado($user, $maintenance);
                $this->testMantenimientoCompletado($user, $maintenance);
                $this->testAlertaUrgente($user, $maintenance);
                break;
        }

        $this->info('✅ Pruebas completadas');
    }

    private function testMantenimientoAsignado(User $user, Maintenance $maintenance)
    {
        $this->info('📧 Probando MantenimientoAsignado (Email + Push)...');

        try {
            $user->notify(new MantenimientoAsignado($maintenance));
            $this->info('✅ Notificación de asignación enviada correctamente');
        } catch (\Exception $e) {
            $this->error("❌ Error enviando notificación de asignación: " . $e->getMessage());
        }
    }

    private function testMantenimientoCompletado(User $user, Maintenance $maintenance)
    {
        $this->info('📱 Probando MantenimientoCompletado (Solo Push)...');

        try {
            $user->notify(new MantenimientoCompletado($maintenance));
            $this->info('✅ Notificación de completado enviada correctamente');
        } catch (\Exception $e) {
            $this->error("❌ Error enviando notificación de completado: " . $e->getMessage());
        }
    }

    private function testAlertaUrgente(User $user, Maintenance $maintenance)
    {
        $this->info('🚨 Probando MaintenanceCreated (Solo Push)...');

        try {
            $user->notify(new MaintenanceCreated($maintenance, 'Mantenimiento crítico detectado - Prueba del sistema'));
            $this->info('✅ Alerta urgente enviada correctamente');
        } catch (\Exception $e) {
            $this->error("❌ Error enviando alerta urgente: " . $e->getMessage());
        }
    }
}
