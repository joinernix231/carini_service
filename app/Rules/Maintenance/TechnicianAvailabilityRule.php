<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TechnicianAvailabilityRule implements ValidationRule
{
    protected $date;
    protected $shift;

    public function __construct($date, $shift)
    {
        $this->date = $date;
        $this->shift = $shift;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return; // Si no hay técnico asignado, no hay conflicto
        }

        $exists = Maintenance::where('technician_id', $value)
            ->where('date_maintenance', $this->date)
            ->where('shift', $this->shift)
            ->whereIn('status', ['assigned', 'in_progress']) // Solo verificar mantenimientos activos
            ->exists();

        if ($exists) {
            $fail('El técnico ya tiene un mantenimiento asignado para esta fecha y turno.');
        }
    }
}
