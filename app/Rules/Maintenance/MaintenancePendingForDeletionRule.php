<?php

namespace App\Rules\Maintenance;

use App\Models\Maintenance\Maintenance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaintenancePendingForDeletionRule implements ValidationRule
{
    protected $maintenance;

    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->maintenance->status !== 'pending') {
            $fail('Solo se pueden eliminar mantenimientos con estado pendiente.');
        }
    }
}
