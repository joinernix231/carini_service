<?php

namespace Database\Factories\Client;

use App\Models\Client\Client;
use App\Models\Client\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::inRandomOrder()->first()->id,
            'nombre_contacto' => $this->faker->name(),
            'correo' => $this->faker->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'direccion' => $this->faker->address(),
            'cargo' => $this->faker->randomElement(['Gerente','Asistente','Ventas','Soporte']),
        ];
    }
}
