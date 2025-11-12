<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaintenanceReassignableStatusRule implements ValidationRule
{
    protected $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedStatuses = ['pending', 'quoted'];
        
        if (!in_array($this->maintenance->status, $allowedStatuses)) {
            $fail('Solo se pueden reasignar mantenimientos con estado pendiente o cotizado.');
        }
    }
}


