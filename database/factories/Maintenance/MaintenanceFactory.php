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
            'client_device_id' => ClientDevice::factory(),
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
}
