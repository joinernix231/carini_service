<?php

namespace Database\Seeders;

use App\Models\Client\Client;
use App\Models\Client\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        Contact::factory()->count(10)->create();
    }
}
