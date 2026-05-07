<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCreated;
use App\Notifications\Maintenance\MantenimientoCreadoParaCliente;
use Illuminate\Support\Facades\Log;

class NotifyClientMaintenanceCreatedListener
{
    /**
     * Handle the event.
     *
     * @param MaintenanceCreated $event
     * @return void
     */
    public function handle(MaintenanceCreated $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client.user',
            'clientDevices.device'
        ]);

        // Verificar que el mantenimiento tenga clientDevices
        if ($maintenance->clientDevices->isEmpty()) {
            Log::warning('No se pudo enviar notificación de mantenimiento creado al cliente: no hay dispositivos asociados', [
                'maintenance_id' => $maintenance->id,
            ]);
            return;
        }

        // Obtener el cliente del primer dispositivo (asumiendo que todos los dispositivos pertenecen al mismo cliente)
        $firstDevice = $maintenance->clientDevices->first();
        $client = $firstDevice->client ?? null;

        if (!$client || !$client->user) {
            Log::warning('No se pudo enviar notificación de mantenimiento creado al cliente: cliente o usuario no encontrado', [
                'maintenance_id' => $maintenance->id,
                'client_id' => $client ? $client->id : null,
            ]);
            return;
        }

        // Enviar notificación al usuario del cliente
        $client->user->notify(new MantenimientoCreadoParaCliente($maintenance));

        Log::info('Notificación de mantenimiento creado enviada al cliente', [
            'maintenance_id' => $maintenance->id,
            'client_id' => $client->id,
            'user_id' => $client->user->id,
        ]);
    }
}

