<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCancelled;
use App\Notifications\Maintenance\MantenimientoCanceladoPorNoConfirmacion;

class NotifyClientMaintenanceCancelledListener
{
    public function handle(MaintenanceCancelled $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
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

        // Enviar notificación al usuario del cliente
        $client->user->notify(new MantenimientoCanceladoPorNoConfirmacion($maintenance));
    }
}

