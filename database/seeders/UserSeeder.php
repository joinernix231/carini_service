<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Seed usuarios base del sistema
     */
    public function run(): void
    {
        // Crear administrador específico
        User::firstOrCreate(
            ['email' => 'talentohumano@carini.co'],
            [
                'name' => 'Adriana Administrador',
                'password' => bcrypt('prueba1234'),
                'role' => 'administrador',
                'policy_accepted_at' => now(),
            ]
        );



        $this->command->info('✅ Usuarios base creados correctamente (admin, cliente, técnico, coordinador).');
    }
}

