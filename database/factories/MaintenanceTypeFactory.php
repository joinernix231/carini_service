<?php

namespace Database\Factories;

use App\Models\Maintenance\MaintenanceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceType>
 */
class MaintenanceTypeFactory extends Factory
{
    protected $model = MaintenanceType::class;

    public function definition(): array
    {
        return [
            'name' => 'General Inspection',
            'value' => 'general_inspection',
        ];
    }
}
