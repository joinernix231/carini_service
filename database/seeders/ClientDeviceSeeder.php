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
        $clients = Client::all();
        $devices = Device::all();

        if ($clients->isEmpty()) {
            $this->command->warn('⚠️ No se encontraron clientes. Ejecute primero ClientSeeder.');
            return;
        }

        if ($devices->isEmpty()) {
            $this->command->warn('⚠️ No se encontraron dispositivos. Ejecute primero DeviceSeeder.');
            return;
        }

        $totalCreated = 0;
        $addresses = [
            'Carrera 15 #45-23, Bogotá',
            'Avenida 68 #12-45, Bogotá',
            'Calle 100 #50-30, Bogotá',
            'Carrera 7 #32-10, Medellín',
            'Avenida El Poblado #25-67, Medellín',
            'Calle 50 #10-20, Cali',
            'Avenida 6N #28-15, Cali',
            'Carrera 1 #5-10, Barranquilla',
            'Calle 72 #41-20, Barranquilla',
        ];

        // Crear relaciones cliente-dispositivo para todos los clientes
        foreach ($clients as $client) {
            // Cada cliente tiene entre 2 y 5 dispositivos
            $deviceCount = rand(2, min(5, $devices->count()));
            $selectedDevices = $devices->random($deviceCount);
            
            foreach ($selectedDevices as $device) {
                // Verificar que no exista ya esta combinación
                $exists = ClientDevice::where('client_id', $client->id)
                    ->where('device_id', $device->id)
                    ->exists();

                if (!$exists) {
                    ClientDevice::create([
                        'client_id' => $client->id,
                        'device_id' => $device->id,
                        'serial' => 'SER-' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                        'status' => rand(0, 10) > 1, // 90% activos
                        'address' => $addresses[array_rand($addresses)],
                        'source' => rand(0, 1) ? 'qr' : 'manual',
                    ]);
                    $totalCreated++;
                }
            }
        }

        $totalClientDevices = ClientDevice::count();
        $this->command->info("✅ {$totalCreated} nuevas relaciones cliente-dispositivo creadas. Total: {$totalClientDevices} dispositivos asignados.");
    }
}
