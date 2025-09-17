<?php

namespace Database\Seeders;

use App\Models\Client\Client;
use App\Models\Technician\Technician;
use Illuminate\Database\Seeder;

class TechnicalSeeder extends Seeder
{
    public function run(): void
    {
        Technician::factory()->count(10)->create();
    }
}
