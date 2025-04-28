<?php

namespace App\Rules\DeviceClient;

use App\Models\Device\Device;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DeviceNotLinkedToClientRule implements ValidationRule
{
    public function __construct(protected Device $device) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->device->clientDevices()->whereNull('deleted_at')->exists()) {
            $fail('El equipo ya está vinculado a un cliente.');
        }
    }

    public function message(): string
    {
        return 'El equipo ya está vinculado a un cliente.';
    }
}
