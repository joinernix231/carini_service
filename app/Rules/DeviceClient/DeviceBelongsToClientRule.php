<?php

namespace App\Rules\DeviceClient;

use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class DeviceBelongsToClientRule implements ValidationRule
{
    public function __construct(private Client $client)
    {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = ClientDevice::where('client_id', $this->client->id)
            ->where('id', $value)
            ->exists();


        if (!$exists) {
            $fail('El equipo no está asociado a este cliente.');
        }
    }
}
