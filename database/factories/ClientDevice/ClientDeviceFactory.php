<?php

namespace Database\Factories\ClientDevice;

use App\Models\ClientDevice\ClientDevice;
use App\Models\Client\Client;
use App\Models\Device\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientDeviceFactory extends Factory
{
    protected $model = ClientDevice::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'device_id' => Device::factory(),
            'serial' => 'SER-' . $this->faker->unique()->numberBetween(100000, 999999),
            'status' => true,
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'source' => $this->faker->randomElement(['qr', 'manual']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
