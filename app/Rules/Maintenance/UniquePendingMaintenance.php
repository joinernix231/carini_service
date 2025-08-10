<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePendingMaintenance implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Maintenance::where('client_device_id', $value)
            ->where('status', '!=', 'complete')
            ->exists();

        if ($exists) {
            $fail('Este equipo ya tiene un mantenimiento activo o en proceso.');
        }
    }
}
