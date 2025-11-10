<?php

namespace Database\Seeders;

use App\Models\Coordinator\Coordinator;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoordinatorSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el usuario coordinador específico (debe existir por UserSeeder)
        $user = User::where('email', 'mauricio@gmail.com')->first();

        if ($user) {
            Coordinator::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'identification' => '12345678',
                    'address' => 'Calle Coordinador 123',
                    'phone' => '3001234567',
                    'status' => 'active',
                ]
            );
        }

        // Crear 5 coordinadores adicionales
        Coordinator::factory(5)->create();

        $totalCoordinators = Coordinator::count();
        $this->command->info("✅ Coordinador específico creado y {$totalCoordinators} coordinadores totales en el sistema.");
    }
}
