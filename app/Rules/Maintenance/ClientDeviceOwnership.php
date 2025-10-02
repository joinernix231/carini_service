<?php

namespace App\Rules\Maintenance;

use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ClientDeviceOwnership implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Client $client */
        $client = session('client');

        if (!$client) {
            $fail('No se pudo identificar el cliente actual.');
            return;
        }

        if (!$value) {
            $fail('El dispositivo es requerido.');
            return;
        }

        $clientDevice = ClientDevice::where('id', $value)
            ->where('client_id', $client->id)
            ->first();

        if (!$clientDevice) {
            $fail('El dispositivo seleccionado no pertenece a tu cuenta o no existe.');
            return;
        }

        if (!$clientDevice->status) {
            $fail('El dispositivo seleccionado no está activo.');
            return;
        }
    }
}
