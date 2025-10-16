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
        return [
            'type' => $this->faker->randomElement(['preventive', 'corrective']),
            'description' => $this->faker->sentence(),
            'status' => 'pending',
            'shift' => $this->faker->optional()->randomElement(['AM', 'PM']),
            'photo' => 'equipo',
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
        return $this->afterCreating(function (Maintenance $maintenance) use ($clientDevices) {
            if ($clientDevices === null) {
                // Si no se especifican dispositivos, crear algunos aleatorios
                $devices = ClientDevice::inRandomOrder()->take(rand(1, 3))->get();
            } else {
                // Asegurar que sea una colección
                $devices = collect($clientDevices);
            }

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
        return $this->afterCreating(function (Maintenance $maintenance) use ($clientDevice) {
            if ($clientDevice === null) {
                $clientDevice = ClientDevice::inRandomOrder()->first();
            }

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
