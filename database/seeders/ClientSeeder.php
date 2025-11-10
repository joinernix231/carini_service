<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el usuario cliente específico (debe existir por UserSeeder)
        $user = User::where('email', 'jo@gmail.com')->first();

        if ($user) {
            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'identifier' => '12345678',
                    'name' => 'Joiner Davila Cliente',
                    'client_type' => 'Natural',
                    'document_type' => 'CC',
                    'address' => 'Calle Siempre Viva 123',
                    'city' => 'Bogotá',
                    'department' => 'Cundinamarca',
                    'phone' => '3001234567',
                    'status' => 'active',
                ]
            );
        }

        // Crear 8 clientes naturales y 7 clientes jurídicos para tener más datos
        Client::factory(8)->natural()->create();
        Client::factory(7)->juridico()->create();

        $totalClients = Client::count();
        $this->command->info("✅ Cliente específico creado y {$totalClients} clientes totales en el sistema (8 naturales, 7 jurídicos).");
    }
}
