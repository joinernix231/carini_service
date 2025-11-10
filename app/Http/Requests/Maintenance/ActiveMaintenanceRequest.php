<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class ActiveMaintenanceRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['tecnico'];
        $user = session('user');

        if (!in_array($user->role, $allowedRoles)) {
            return false;
        }

        // Verificar que el técnico esté autenticado
        if (!session('technician_id')) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
