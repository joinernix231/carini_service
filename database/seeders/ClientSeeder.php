<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Crear administrador específico
        User::firstOrCreate(
            ['email' => 'joinernix2@gmail.com'],
            [
                'name' => 'Joiner Administrador',
                'password' => bcrypt('saiz123'),
                'role' => 'administrador',
            ]
        );

        // Crear cliente específico
        $user = User::firstOrCreate(
            ['email' => 'jo@gmail.com'],
            [
                'name' => 'Joiner Davila Cliente',
                'password' => bcrypt('saiz123'),
                'role' => 'cliente',
            ]
        );

        Client::firstOrCreate(
            ['user_id' => $user->id],
            [
                'identifier' => '12345678',
                'name' => 'Joiner Davila Cliente',
                'client_type' => 'Natural',
                'document_type' => 'CC',
                'address' => 'Calle Siempre Viva 123',
                'city' => 'Bogotá',
                'phone' => '3001234567',
            ]
        );

        // Crear 5 clientes naturales y 4 clientes jurídicos
        Client::factory(5)->natural()->create();
        Client::factory(4)->juridico()->create();

        $this->command->info('✅ Administrador con email joinernix2@gmail.com, cliente fijo con email jo@gmail.com, 5 clientes naturales y 4 clientes jurídicos agregados correctamente.');
    }
}
