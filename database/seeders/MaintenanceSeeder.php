<?php

namespace Database\Seeders;

use App\Models\Maintenance\Maintenance;
use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener algunos dispositivos de clientes
        $clientDevices = ClientDevice::with(['client', 'device'])->get();

        if ($clientDevices->count() > 0) {
            $maintenanceCount = 0;

            // Crear algunos mantenimientos con un solo dispositivo
            $singleDeviceMaintenances = min(3, $clientDevices->count());
            for ($i = 0; $i < $singleDeviceMaintenances; $i++) {
                Maintenance::factory()
                    ->withSingleDevice($clientDevices->random())
                    ->create([
                        'status' => 'pending',
                        'type' => 'preventive',
                    ]);
                $maintenanceCount++;
            }

            // Crear algunos mantenimientos con múltiples dispositivos
            if ($clientDevices->count() >= 2) {
                $multiDeviceMaintenances = min(2, $clientDevices->count() / 2);
                for ($i = 0; $i < $multiDeviceMaintenances; $i++) {
                    $selectedDevices = $clientDevices->random(rand(2, min(4, $clientDevices->count())));
                    Maintenance::factory()
                        ->withDevices($selectedDevices)
                        ->create([
                            'status' => 'pending',
                            'type' => 'corrective',
                        ]);
                    $maintenanceCount++;
                }
            }

            // Crear algunos mantenimientos con diferentes estados
            $statuses = ['assigned', 'in_progress', 'completed'];
            foreach ($statuses as $status) {
                if ($clientDevices->count() > 0) {
                    Maintenance::factory()
                        ->withSingleDevice($clientDevices->random())
                        ->create([
                            'status' => $status,
                            'type' => Faker::create()->randomElement(['preventive', 'corrective']),
                        ]);
                    $maintenanceCount++;
                }
            }

            $this->command->info("✅ {$maintenanceCount} mantenimientos de prueba creados correctamente con la nueva estructura muchos a muchos.");
        } else {
            $this->command->warn('⚠️ No se encontraron dispositivos de clientes. Ejecute primero ClientSeeder, DeviceSeeder y ClientDeviceSeeder.');
        }
    }
}
