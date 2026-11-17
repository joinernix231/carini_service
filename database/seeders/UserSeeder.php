<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed usuarios base del sistema
     */
    public function run(): void
    {
        // El repositorio es público: la contraseña del administrador no puede
        // estar escrita aquí. Se toma de SEED_ADMIN_PASSWORD o se genera una
        // aleatoria y se muestra una sola vez en consola.
        $password = env('SEED_ADMIN_PASSWORD') ?: Str::random(20);

        // Crear administrador específico (si ya existe, no se toca su contraseña)
        $admin = User::firstOrCreate(
            ['email' => 'talentohumano@carini.co'],
            [
                'name' => 'Adriana Administrador',
                'password' => bcrypt($password),
                'role' => 'administrador',
                'policy_accepted_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated && !env('SEED_ADMIN_PASSWORD')) {
            $this->command->warn("Contraseña generada para {$admin->email}: {$password}");
        }

        $this->command->info('✅ Usuarios base creados correctamente (admin, cliente, técnico, coordinador).');
    }
}

