<?php

namespace Database\Factories;

use App\Models\Device\Device;
use App\Models\Technician\Technician;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class TechnicalFactory extends Factory
{
    protected $model = Technician::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tecnico(),
            'document' => $this->faker->unique()->numerify('##########'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['active', 'inactive']),

            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'photo' => 'tecnico',

            'specialty' => $this->faker->randomElement([
                'Mantenimiento Industrial',
                'Mantenimiento Eléctrico',
                'Mantenimiento Mecánico',
                'Mantenimiento de Equipos',
                'Técnico en Refrigeración',
                'Técnico en Lavandería Industrial'
            ]),
            'hire_date' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'contract_type' => $this->faker->randomElement(['full_time', 'part_time', 'contractor']),

            'eps_pdf' => 'manual_equipo_1758606277245_rpvbcpa2ran',
            'arl_pdf' => 'manual_equipo_1758606277245_rpvbcpa2ran',
            'pension_pdf' => 'manual_equipo_1758606277245_rpvbcpa2ran'
        ];
    }
}
