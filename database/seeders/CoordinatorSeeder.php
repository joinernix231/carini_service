<?php

namespace Database\Seeders;

use App\Models\Coordinator\Coordinator;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoordinatorSeeder extends Seeder
{
    public function run(): void
    {
        // Crear coordinador específico
        $user = User::firstOrCreate(
            ['email' => 'mauricio@gmail.com'],
            [
                'name' => 'Mauricio Coordinador',
                'password' => bcrypt('saiz123'),
                'role' => 'coordinador',
            ]
        );

        Coordinator::firstOrCreate(
            ['user_id' => $user->id],
            [
                'identification' => '12345678',
                'address' => 'Calle Coordinador 123',
                'phone' => '3001234567',
                'status' => 'active',
            ]
        );

        // Crear 9 coordinadores adicionales de prueba
        Coordinator::factory(9)->create();

        $this->command->info('✅ Coordinador fijo con email mauricio@gmail.com y 9 coordinadores de prueba agregados correctamente.');
    }
}
