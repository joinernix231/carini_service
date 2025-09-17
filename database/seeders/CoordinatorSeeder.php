<?php

namespace Database\Seeders;

use App\Models\Coordinator\Coordinator;
use Illuminate\Database\Seeder;

class CoordinatorSeeder extends Seeder
{
    public function run(): void
    {
        Coordinator::factory()->count(10)->create();
    }
}
