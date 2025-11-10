<?php

namespace Database\Seeders;

use App\Models\Technician\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;

class TechnicalSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el usuario técnico específico (debe existir por UserSeeder)
        $user = User::where('email', 'eyder@gmail.com')->first();

        if ($user) {
            Technician::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'document' => '12345678',
                    'phone' => '3007654321',
                    'address' => 'Calle Técnico 456',
                    'status' => 'active',
                    'blood_type' => 'O+',
                    'photo' => 'tecnico',
                    'specialty' => 'Técnico en Lavandería Industrial',
                    'hire_date' => now()->subYear(),
                    'contract_type' => 'full_time',
                    'eps_pdf' => 'manual_equipo_1758606277245_rpvbcpa2ran',
                    'arl_pdf' => 'manual_equipo_1758606277245_rpvbcpa2ran',
                    'pension_pdf' => 'manual_equipo_1758606277245_rpvbcpa2ran',
                ]
            );
        }

        // Crear 12 técnicos adicionales para tener más opciones
        Technician::factory(12)->create();

        $totalTechnicians = Technician::count();
        $this->command->info("✅ Técnico específico creado y {$totalTechnicians} técnicos totales en el sistema.");
    }
}
