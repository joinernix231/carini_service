<?php

namespace Database\Seeders;

use App\Models\Client\Client;
use App\Models\Client\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->warn('⚠️ No se encontraron clientes. Ejecute primero ClientSeeder.');
            return;
        }

        // Crear 2-3 contactos por cliente para tener datos más realistas
        foreach ($clients as $client) {
            $contactCount = rand(2, 3);
            Contact::factory($contactCount)->create([
                'client_id' => $client->id,
            ]);
        }

        $totalContacts = Contact::count();
        $this->command->info("✅ {$totalContacts} contactos creados correctamente para {$clients->count()} clientes.");
    }
}
