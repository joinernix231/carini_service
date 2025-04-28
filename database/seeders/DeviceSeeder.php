<?php

namespace Database\Seeders;

use App\Models\Client\Client;
use App\Models\Device\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        Device::factory()->count(10)->create();
    }
}
