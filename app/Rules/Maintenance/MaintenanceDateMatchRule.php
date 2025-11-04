<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaintenanceDateMatchRule implements ValidationRule
{
    protected $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si el mantenimiento no tiene fecha programada, no validar
        if (!$this->maintenance->date_maintenance) {
            return;
        }

        $today = now()->startOfDay();
        $scheduledDate = $this->maintenance->date_maintenance->startOfDay();

        if (!$today->equalTo($scheduledDate)) {
            $scheduledDateFormatted = $scheduledDate->format('d/m/Y');
            $todayFormatted = $today->format('d/m/Y');
            
            $fail("No se puede iniciar el mantenimiento. La fecha programada es {$scheduledDateFormatted}, pero hoy es {$todayFormatted}.");
        }
    }
}

