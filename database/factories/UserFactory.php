<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('saiz123'),
            'remember_token' => Str::random(10),
            'role' => 'cliente', // Rol por defecto
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Crear un usuario con rol de cliente
     */
    public function cliente(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'cliente',
        ]);
    }

    /**
     * Crear un usuario con rol de técnico
     */
    public function tecnico(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'tecnico',
        ]);
    }

    /**
     * Crear un usuario con rol de coordinador
     */
    public function coordinador(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'coordinador',
        ]);
    }
}
