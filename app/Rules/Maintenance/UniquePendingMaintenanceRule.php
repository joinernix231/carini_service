<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePendingMaintenanceRule implements ValidationRule
{
    protected $clientDeviceId;

    public function __construct(int $clientDeviceId)
    {
        $this->clientDeviceId = $clientDeviceId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Verificar si ya existe un mantenimiento pendiente para este equipo
        $existingPendingMaintenance = Maintenance::where('client_device_id', $this->clientDeviceId)
            ->where('status', 'pending')
            ->exists();

        if ($existingPendingMaintenance) {
            $fail('Ya existe un mantenimiento pendiente para este equipo. No se puede crear otro hasta que se complete el actual.');
        }
    }
}
