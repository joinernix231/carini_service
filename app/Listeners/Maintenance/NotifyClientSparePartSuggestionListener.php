<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\SparePartSuggestionCreated;
use App\Notifications\SugerenciaRepuestoCreada;
use Illuminate\Support\Facades\Log;

class NotifyClientSparePartSuggestionListener
{
    public function handle(SparePartSuggestionCreated $event): void
    {
        $sparePartSuggestion = $event->sparePartSuggestion;
        $sparePartSuggestion->load([
            'maintenance.technician.user',
            'maintenance.clientDevices.client',
            'clientDevice.device'
        ]);

        // Obtener el cliente desde el mantenimiento
        $maintenance = $sparePartSuggestion->maintenance;
        $client = $maintenance->client;

        // Verificar que el cliente existe y tiene un usuario asociado
        if ($client && $client->user) {
            try {
                $client->user->notify(new SugerenciaRepuestoCreada($sparePartSuggestion));
            } catch (\Exception $e) {
                Log::error('Error enviando notificación de sugerencia de repuesto al cliente: ' . $e->getMessage(), [
                    'suggestion_id' => $sparePartSuggestion->id,
                    'maintenance_id' => $maintenance->id,
                    'client_id' => $client->id,
                ]);
            }
        } else {
            Log::warning('No se pudo enviar notificación de sugerencia de repuesto: cliente o usuario no encontrado', [
                'suggestion_id' => $sparePartSuggestion->id,
                'maintenance_id' => $maintenance->id,
            ]);
        }
    }
}
