<?php

namespace Database\Seeders;

use App\Models\Device\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $brand = 'carini';
        $photo = 'equipo';

        // Modelos desde 25LB hasta 100LB en pasos de 5 (25, 30, 35, ... 100)
        $lbs = range(25, 100, 5);

        // Lavadoras: bassina <LB>
        foreach ($lbs as $lb) {
            $model = "bassina {$lb}";

            Device::firstOrCreate(
                ['brand' => $brand, 'model' => $model, 'type' => 'lavadora'],
                [
                    'photo' => $photo,
                    'description' => "Lavadora {$model} (por capacidad {$lb} LB)",
                ]
            );
        }

        // Secadoras: Sole-<LB>
        foreach ($lbs as $lb) {
            $model = "Sole-{$lb}";

            Device::firstOrCreate(
                ['brand' => $brand, 'model' => $model, 'type' => 'secadora'],
                [
                    'photo' => $photo,
                    'description' => "Secadora {$model} (por capacidad {$lb} LB)",
                ]
            );
        }
    }
}
