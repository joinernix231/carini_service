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
        // Validar que el mantenimiento está asignado al técnico actual
        if ($this->maintenance->technician_id !== session('technician_id')) {
            $fail('Este mantenimiento no está asignado a ti.');
            return;
        }

        // Validar que el mantenimiento tiene el estado correcto
        // Permite 'assigned' o 'in_progress' (cuando ya se marcó como "en camino")
        if (!in_array($this->maintenance->status, ['assigned', 'in_progress'])) {
            $fail('Solo se pueden iniciar mantenimientos con estado "assigned" o "in_progress". El estado actual es: ' . $this->maintenance->status);
            return;
        }

        // Validar que el mantenimiento esté confirmado si requiere confirmación
        if ($this->maintenance->confirmation_required && $this->maintenance->confirmed_at === null) {
            $fail('El cliente aún no ha confirmado este mantenimiento. No puedes iniciarlo.');
            return;
        }
    }
}

