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
        $clientType = $this->faker->randomElement(['Natural', 'Jurídico']);
        
        return [
            'identifier' => $this->faker->unique()->numerify('########-#'),
            'name' => $this->faker->name,
            'client_type' => $clientType,
            'document_type' => $clientType === 'Jurídico' ? 'NIT' : $this->faker->randomElement(['CC', 'CE', 'CI', 'PASS']),
            'legal_representative' => $clientType === 'Jurídico' ? $this->faker->name : null,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'department' => $this->faker->citySuffix,
            'phone' => $this->faker->phoneNumber,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Crear un cliente de tipo Natural
     */
    public function natural(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_type' => 'Natural',
            'document_type' => $this->faker->randomElement(['CC', 'CE', 'CI', 'PASS']),
            'legal_representative' => null,
        ]);
    }

    /**
     * Crear un cliente de tipo Jurídico
     */
    public function juridico(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_type' => 'Jurídico',
            'document_type' => 'NIT',
            'legal_representative' => $this->faker->name,
        ]);
    }
}
