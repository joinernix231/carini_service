<?php

namespace Database\Seeders;

use App\Models\Maintenance\Maintenance;
use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener algunos clientes y sus dispositivos
        $clientDevices = ClientDevice::with(['client', 'device'])->take(5)->get();

        if ($clientDevices->count() > 0) {
            // Crear mantenimientos de prueba
            foreach ($clientDevices as $clientDevice) {
                Maintenance::factory()->count(2)->create([
                    'client_device_id' => $clientDevice->id,
                    'status' => 'pending',
                ]);
            }

            $this->command->info('✅ 10 mantenimientos de prueba creados correctamente.');
        } else {
            $this->command->warn('⚠️ No se encontraron dispositivos de clientes. Ejecute primero ClientSeeder y DeviceSeeder.');
        }
    }
}
