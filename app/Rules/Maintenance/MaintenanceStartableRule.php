<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaintenanceStartableRule implements ValidationRule
{
    protected $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->maintenance->technician_id !== session('technician_id')) {
            $fail('Este mantenimiento no está asignado a ti.');
            return;
        }

        if ($this->maintenance->status !== 'assigned') {
            $fail('Solo se pueden iniciar mantenimientos con estado "assigned". El estado actual es: ' . $this->maintenance->status);
            return;
        }

        if ($this->maintenance->confirmation_required && $this->maintenance->confirmed_at === null) {
            $fail('El cliente aún no ha confirmado este mantenimiento. No puedes iniciarlo.');
            return;
        }
    }
}

