<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceAssigned;
use App\Notifications\Maintenance\TecnicoAsignadoRequiereConfirmacion;

class NotifyClientMaintenanceAssignedListener
{
    public function handle(MaintenanceAssigned $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'technician.user',
            'clientDevices.client.user',
            'clientDevices.device'
        ]);

        // Verificar que el mantenimiento tenga clientDevices
        if ($maintenance->clientDevices->isEmpty()) {
            return;
        }

        // Obtener el cliente del primer dispositivo (asumiendo que todos los dispositivos pertenecen al mismo cliente)
        $firstDevice = $maintenance->clientDevices->first();
        $client = $firstDevice->client ?? null;

        if (!$client || !$client->user) {
            return;
        }

        // Solo notificar si requiere confirmación
        if ($maintenance->confirmation_required) {
            // Enviar notificación al usuario del cliente
            $client->user->notify(new TecnicoAsignadoRequiereConfirmacion($maintenance));
        }
    }
}

