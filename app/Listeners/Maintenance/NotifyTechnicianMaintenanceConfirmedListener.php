<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceConfirmed;
use App\Notifications\Maintenance\MantenimientoConfirmadoPorCliente;

class NotifyTechnicianMaintenanceConfirmedListener
{
    public function handle(MaintenanceConfirmed $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'technician.user',
            'clientDevices.client',
            'clientDevices.device'
        ]);

        // Verificar que el mantenimiento tenga un técnico asignado
        if (!$maintenance->technician || !$maintenance->technician->user) {
            return;
        }

        // Enviar notificación al usuario del técnico
        $maintenance->technician->user->notify(new MantenimientoConfirmadoPorCliente($maintenance));
    }
}
