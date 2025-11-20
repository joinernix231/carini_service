<?php

namespace App\Rules\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DevicesWithinRadiusRule implements ValidationRule
{
    /**
     * Radio máximo permitido en kilómetros
     */
    private const MAX_RADIUS_KM = 5;

    /**
     * Radio de la Tierra en kilómetros
     */
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $clientDevices = request()->input('client_devices', []);
        
        if (empty($clientDevices) || count($clientDevices) < 2) {
            // Si hay menos de 2 equipos, no hay nada que validar
            return;
        }

        $deviceIds = array_column($clientDevices, 'id');
        
        // Obtener todos los client devices con sus coordenadas y la relación device
        $devices = ClientDevice::whereIn('id', $deviceIds)
            ->with('device')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Verificar que todos los equipos tengan coordenadas
        if ($devices->count() !== count($deviceIds)) {
            $missingDeviceIds = array_diff($deviceIds, $devices->pluck('id')->toArray());
            
            // Obtener los modelos de los equipos sin coordenadas
            $missingDevices = ClientDevice::whereIn('id', $missingDeviceIds)
                ->with('device')
                ->get();
            
            $deviceModels = $missingDevices->map(function ($clientDevice) {
                return $clientDevice->device->model ?? 'Sin modelo';
            })->toArray();
            
            $fail('Los siguientes equipos no tienen coordenadas de ubicación registradas: ' . implode(', ', $deviceModels) . '. Por favor, actualiza la ubicación de estos equipos antes de crear el mantenimiento.');
            return;
        }

        // Verificar que todos los equipos estén dentro del radio permitido
        for ($i = 0; $i < $devices->count(); $i++) {
            for ($j = $i + 1; $j < $devices->count(); $j++) {
                $device1 = $devices[$i];
                $device2 = $devices[$j];

                $distance = $this->calculateDistance(
                    $device1->latitude,
                    $device1->longitude,
                    $device2->latitude,
                    $device2->longitude
                );

                if ($distance > self::MAX_RADIUS_KM) {
                    $model1 = $device1->device->model ?? 'Sin modelo';
                    $model2 = $device2->device->model ?? 'Sin modelo';
                    
                    $fail(sprintf(
                        'Los equipos "%s" y "%s" están separados por %.2f km, lo cual excede el radio máximo permitido de %d km. Todos los equipos deben estar dentro de un radio de %d km entre sí.',
                        $model1,
                        $model2,
                        $distance,
                        self::MAX_RADIUS_KM,
                        self::MAX_RADIUS_KM
                    ));
                    return;
                }
            }
        }
    }

    /**
     * Calcula la distancia entre dos puntos geográficos usando la fórmula de Haversine
     * 
     * @param float $lat1 Latitud del primer punto
     * @param float $lon1 Longitud del primer punto
     * @param float $lat2 Latitud del segundo punto
     * @param float $lon2 Longitud del segundo punto
     * @return float Distancia en kilómetros
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Convertir grados a radianes
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Diferencia de latitud y longitud
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        // Fórmula de Haversine
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Distancia en kilómetros
        return self::EARTH_RADIUS_KM * $c;
    }
}

