<?php

namespace Database\Seeders;

use App\Models\Client\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->count(10)->create();
    }
}
