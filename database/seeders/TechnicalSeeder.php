<?php

namespace Database\Seeders;

use App\Models\Technician\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;

class TechnicalSeeder extends Seeder
{
    public function run(): void
    {
        // Crear técnico específico
        $user = User::firstOrCreate(
            ['email' => 'eyder@gmail.com'],
            [
                'name' => 'Eyder Técnico',
                'password' => bcrypt('saiz123'),
                'role' => 'tecnico',
            ]
        );

        Technician::firstOrCreate(
            ['user_id' => $user->id],
            [
                'document' => '87654321',
                'phone' => '3007654321',
                'address' => 'Calle Técnico 456',
                'status' => 'active',
            ]
        );

        // Crear 9 técnicos adicionales de prueba
        Technician::factory(9)->create();

        $this->command->info('✅ Técnico fijo con email eyder@gmail.com y 9 técnicos de prueba agregados correctamente.');
    }
}
