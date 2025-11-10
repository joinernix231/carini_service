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
            ['email' => 'joinernix2@gmail.com'],
            [
                'name' => 'Joiner Administrador',
                'password' => bcrypt('saiz123'),
                'role' => 'administrador',
                'policy_accepted_at' => now(),
            ]
        );

        // Crear cliente específico (será usado por ClientSeeder)
        User::firstOrCreate(
            ['email' => 'jo@gmail.com'],
            [
                'name' => 'Joiner Davila Cliente',
                'password' => bcrypt('saiz123'),
                'role' => 'cliente',
                'policy_accepted_at' => now(),
            ]
        );

        // Crear técnico específico (será usado por TechnicalSeeder)
        User::firstOrCreate(
            ['email' => 'eyder@gmail.com'],
            [
                'name' => 'Eyder Técnico',
                'password' => bcrypt('saiz123'),
                'role' => 'tecnico',
                'policy_accepted_at' => now(),
            ]
        );

        // Crear coordinador específico (será usado por CoordinatorSeeder)
        User::firstOrCreate(
            ['email' => 'mauricio@gmail.com'],
            [
                'name' => 'Mauricio Coordinador',
                'password' => bcrypt('saiz123'),
                'role' => 'coordinador',
                'policy_accepted_at' => now(),
            ]
        );

        $this->command->info('✅ Usuarios base creados correctamente (admin, cliente, técnico, coordinador).');
    }
}

