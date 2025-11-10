<?php

namespace Database\Factories\Maintenance;

use App\Models\Maintenance\Maintenance;
use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        $types = ['preventive', 'corrective'];
        $type = $this->faker->randomElement($types);
        
        $descriptions = [
            'preventive' => [
                'Mantenimiento preventivo programado',
                'Revisión general de componentes',
                'Limpieza y lubricación de partes móviles',
                'Verificación de sistemas eléctricos',
                'Calibración de sensores y controles',
                'Inspección de mangueras y conexiones',
                'Revisión de filtros y sistemas de agua',
            ],
            'corrective' => [
                'Reparación de motor defectuoso',
                'Cambio de componentes desgastados',
                'Corrección de falla en sistema eléctrico',
                'Reparación de fuga en sistema hidráulico',
                'Sustitución de piezas dañadas',
                'Corrección de error en programación',
                'Reparación de sistema de secado',
            ],
        ];

        // Obtener un ClientDevice aleatorio para client_device_id (requerido por la migración)
        $clientDevice = ClientDevice::inRandomOrder()->first();

        return [
            'client_device_id' => $clientDevice ? $clientDevice->id : null,
            'type' => $type,
            'description' => $this->faker->randomElement($descriptions[$type]),
            'status' => 'pending',
            'date_maintenance' => $this->faker->dateTimeBetween('now', '+30 days'),
            'shift' => $this->faker->randomElement(['AM', 'PM']),
            'photo' => 'equipo',
            'value' => $this->faker->randomFloat(2, 50000, 500000),
            'spare_parts' => $this->faker->optional(0.7)->randomElements([
                'Filtro de agua',
                'Manguera de entrada',
                'Sensor de temperatura',
                'Resistencia eléctrica',
                'Bomba de agua',
                'Correa de transmisión',
                'Rodamiento',
                'Válvula solenoide',
            ], rand(1, 4)),
            'observations' => $this->faker->optional(0.5)->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Configurar el factory para crear un mantenimiento con dispositivos específicos.
     */
    public function withDevices($clientDevices = null): static
    {
        // Normalizar los dispositivos una sola vez
        if ($clientDevices === null) {
            $devices = ClientDevice::inRandomOrder()->take(rand(1, 3))->get();
        } else {
            $devices = collect($clientDevices);
        }

        return $this->state(function (array $attributes) use ($devices) {
            // Establecer client_device_id con el primer dispositivo (requerido por la migración)
            if ($devices->isNotEmpty()) {
                $attributes['client_device_id'] = $devices->first()->id;
            }

            return $attributes;
        })->afterCreating(function (Maintenance $maintenance) use ($devices) {
            $pivotData = [];
            foreach ($devices as $device) {
                // Obtener el ID del dispositivo
                $deviceId = $device->id;
                $pivotData[$deviceId] = [
                    'description' => $this->faker->optional()->sentence(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $maintenance->clientDevices()->attach($pivotData);
        });
    }

    /**
     * Configurar el factory para crear un mantenimiento con un solo dispositivo.
     */
    public function withSingleDevice(ClientDevice $clientDevice = null): static
    {
        // Obtener el dispositivo una sola vez
        if ($clientDevice === null) {
            $clientDevice = ClientDevice::inRandomOrder()->first();
        }

        return $this->state(function (array $attributes) use ($clientDevice) {
            // Establecer client_device_id (requerido por la migración)
            if ($clientDevice) {
                $attributes['client_device_id'] = $clientDevice->id;
            }

            return $attributes;
        })->afterCreating(function (Maintenance $maintenance) use ($clientDevice) {
            if ($clientDevice) {
                $maintenance->clientDevices()->attach([
                    $clientDevice->id => [
                        'description' => $this->faker->optional()->sentence(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
            }
        });
    }
}
