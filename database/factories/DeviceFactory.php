<?php

namespace Database\Factories;

use App\Models\Device\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'serial' => $this->faker->unique()->bothify('SN-####-???'),
            'model' => $this->faker->word(),
            'brand' => $this->faker->company(),
            'description' => $this->faker->optional()->sentence(),
            'type' => $this->faker->word(),
            'manufactured_at' => $this->faker->date('Y-m-d'),
        ];
    }
}
