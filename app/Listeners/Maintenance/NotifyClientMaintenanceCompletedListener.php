<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceCompleted;
use App\Notifications\MantenimientoCompletado;
use Illuminate\Support\Facades\Log;

class NotifyClientMaintenanceCompletedListener
{
    /**
     * Handle the event.
     *
     * @param MaintenanceCompleted $event
     * @return void
     */
    public function handle(MaintenanceCompleted $event): void
    {
        $maintenance = $event->maintenance;
        
        // Cargar todas las relaciones necesarias
        $maintenance->load([
            'clientDevices.client.user',
            'clientDevices.device',
            'technician.user'
        ]);

        // Verificar que el mantenimiento tenga clientDevices
        if ($maintenance->clientDevices->isEmpty()) {
            Log::warning('No se pudo enviar notificación de mantenimiento completado: no hay dispositivos asociados', [
                'maintenance_id' => $maintenance->id,
            ]);
            return;
        }

        // Obtener el cliente del primer dispositivo (asumiendo que todos los dispositivos pertenecen al mismo cliente)
        $firstDevice = $maintenance->clientDevices->first();
        $client = $firstDevice->client ?? null;

        if (!$client || !$client->user) {
            Log::warning('No se pudo enviar notificación de mantenimiento completado: cliente o usuario no encontrado', [
                'maintenance_id' => $maintenance->id,
                'client_id' => $client ? $client->id : null,
            ]);
            return;
        }

        // Enviar notificación al usuario del cliente
        try {
            $client->user->notify(new MantenimientoCompletado($maintenance));
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de mantenimiento completado al cliente: ' . $e->getMessage(), [
                'maintenance_id' => $maintenance->id,
                'client_id' => $client->id,
                'error' => $e->getTraceAsString(),
            ]);
        }
    }
}

