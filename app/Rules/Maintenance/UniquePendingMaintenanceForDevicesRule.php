<?php

namespace App\Rules\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePendingMaintenanceForDevicesRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Obtener todos los IDs de dispositivos del request
        $clientDevices = request()->input('client_devices', []);
        
        if (empty($clientDevices)) {
            return;
        }

        $deviceIds = array_column($clientDevices, 'id');
        
        // Verificar si ya existe un mantenimiento pendiente para alguno de estos equipos
        // usando la nueva relación muchos a muchos
        $existingPendingMaintenances = Maintenance::whereHas('clientDevices', function ($query) use ($deviceIds) {
            $query->whereIn('client_device_id', $deviceIds);
        })->where('status', 'pending')->get();

        if ($existingPendingMaintenances->isNotEmpty()) {
            // Obtener los IDs de dispositivos que ya tienen mantenimientos pendientes
            $deviceIdsWithPending = [];
            foreach ($existingPendingMaintenances as $maintenance) {
                $maintenanceDeviceIds = $maintenance->clientDevices->pluck('id')->toArray();
                $deviceIdsWithPending = array_merge($deviceIdsWithPending, $maintenanceDeviceIds);
            }
            
            // Filtrar solo los dispositivos que están en la solicitud actual
            $conflictingDeviceIds = array_intersect($deviceIds, $deviceIdsWithPending);
            
            if (!empty($conflictingDeviceIds)) {
                // Obtener los modelos de los equipos con conflictos
                $conflictingDevices = ClientDevice::whereIn('id', $conflictingDeviceIds)
                    ->with('device')
                    ->get();
                
                $deviceModels = $conflictingDevices->map(function ($clientDevice) {
                    return $clientDevice->device->model ?? 'Sin modelo';
                })->toArray();
                
                $fail('Ya existen mantenimientos pendientes para los siguientes equipos: ' . implode(', ', $deviceModels) . '. No se puede crear otro hasta que se completen los actuales.');
            }
        }
    }
}
