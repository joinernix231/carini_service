<?php

namespace Database\Factories;

use App\Models\Coordinator\Coordinator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coordinator>
 */
class CoordinatorFactory extends Factory
{
    protected $model = Coordinator::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'identification' => $this->faker->unique()->numerify('##########'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
