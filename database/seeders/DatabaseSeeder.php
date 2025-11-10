<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Orden correcto respetando dependencias:
     * 1. Users (base para todos los roles)
     * 2. Clients y Devices (independientes)
     * 3. ClientDevices (depende de Clients y Devices)
     * 4. Contacts (depende de Clients)
     * 5. Technicians y Coordinators (dependen de Users)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,           // 1. Usuarios base
            ClientSeeder::class,          // 2. Clientes
            DeviceSeeder::class,         // 2. Dispositivos
            ClientDeviceSeeder::class,    // 3. Relaciones cliente-dispositivo
            ContactSeeder::class,         // 4. Contactos de clientes
            TechnicalSeeder::class,       // 5. Técnicos
            CoordinatorSeeder::class,     // 5. Coordinadores
        ]);
    }
}
