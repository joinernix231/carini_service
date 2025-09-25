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
            foreach ($clientDevices as $clientDevice) {
                Maintenance::factory()->create([
                    'client_device_id' => $clientDevice->id,
                    'status' => 'pending',
                ]);
            }

            $this->command->info('✅ ' . $clientDevices->count() . ' mantenimientos de prueba creados correctamente (1 por equipo).');
        } else {
            $this->command->warn('⚠️ No se encontraron dispositivos de clientes. Ejecute primero ClientSeeder y DeviceSeeder.');
        }
    }
}
