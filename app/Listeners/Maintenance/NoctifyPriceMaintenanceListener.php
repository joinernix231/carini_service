<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\UploadPriceMaintenance;
use App\Notifications\MantenimientoCotizado;

class NoctifyPriceMaintenanceListener
{
    public function handle(UploadPriceMaintenance $event): void
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

        // Notificar al usuario del cliente cuando un coordinador sube una cotización
        $client->user->notify(new MantenimientoCotizado($maintenance));
    }
}
