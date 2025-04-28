<?php

namespace Database\Factories\Client;


use App\Models\Client\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;
    public function definition(): array
    {
        return [
            'identifier' => $this->faker->unique()->numerify('########-#'),
            'legal_representative' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'phone' => $this->faker->phoneNumber,
            'client_type' => $this->faker->randomElement(['empresa', 'institucional']),
            'user_id' => User::factory(),
        ];
    }
}
