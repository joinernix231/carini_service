<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaintenancePaymentStatusRule implements ValidationRule
{
    protected Maintenance $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($this->maintenance->is_paid)) {
            $fail('Este mantenimiento no requiere pago.');
            return;
        }

        if ($this->maintenance->is_paid === true) {
            $fail('Este mantenimiento ya está pagado.');
            return;
        }

        if ($this->maintenance->status !== 'assigned') {
            $fail('El pago solo puede aprobarse si el mantenimiento está asignado.');
        }
    }
}
