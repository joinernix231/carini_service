<?php

namespace Database\Seeders;

use App\Models\Client\Client;
use App\Models\Device\Device;
use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Seeder;

class ClientDeviceSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener algunos clientes y dispositivos
        $clients = Client::take(5)->get();
        $devices = Device::take(10)->get();

        if ($clients->count() > 0 && $devices->count() > 0) {
            // Crear relaciones cliente-dispositivo
            foreach ($clients as $client) {
                // Asignar 2-3 dispositivos aleatorios a cada cliente
                $randomDevices = $devices->random(rand(2, 3));
                
                foreach ($randomDevices as $device) {
                    ClientDevice::firstOrCreate([
                        'client_id' => $client->id,
                        'device_id' => $device->id,
                    ], [
                        'serial' => 'SER-' . rand(100000, 999999),
                        'status' => true,
                        'address' => 'Dirección de instalación ' . rand(1, 100),
                        'source' => 'manual',
                    ]);
                }
            }

            $this->command->info('✅ Relaciones cliente-dispositivo creadas correctamente.');
        } else {
            $this->command->warn('⚠️ No se encontraron clientes o dispositivos. Ejecute primero ClientSeeder y DeviceSeeder.');
        }
    }
}
