<?php

namespace Database\Seeders;

use App\Models\Maintenance\MaintenanceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaintenanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Diagnostico Electrico', 'value' => 'electrical_diagnosis', 'description' => 'Diagnóstico del sistema eléctrico'],
            ['name' => 'Inspecion General', 'value' => 'general_inspection', 'description' => 'Revisión general del sistema'],
            ['name' => 'Cambio de Repuesto', 'value' => 'spare_part_replacement', 'description' => 'Sustitución de piezas defectuosas'],
            ['name' => 'Ajuste de temperatura', 'value' => 'temperature_adjustment', 'description' => 'Configuración de temperatura adecuada'],
        ];


        foreach ($types as $type) {
            MaintenanceType::firstOrCreate(['value' => $type['value']], $type);
        }
    }
}
