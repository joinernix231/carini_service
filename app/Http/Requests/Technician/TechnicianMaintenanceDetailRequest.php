<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;

class TechnicianMaintenanceDetailRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador','administrador'];
        $user = auth()->user();

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}

