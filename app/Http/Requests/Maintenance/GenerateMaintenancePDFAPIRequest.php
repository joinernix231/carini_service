<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class GenerateMaintenancePDFAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador', 'tecnico'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {
        return [];
    }
}

