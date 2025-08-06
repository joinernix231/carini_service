<?php

namespace App\Rules\Technician;

use App\Repositories\Technician\TechnicianRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class ValidateDateOfTechnician implements ValidationRule
{
    public function __construct(private TechnicianRepository $technicianRepository)
    {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $available = $this->technicianRepository->getAvailableTechnicianForShift($value);

        if (!$available) {
            $fail("No hay técnicos disponibles para la fecha seleccionada ($value).");
        }
    }
}
