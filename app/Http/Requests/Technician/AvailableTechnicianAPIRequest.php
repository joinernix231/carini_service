<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;

class AvailableTechnicianAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
            'shift' => ['nullable', 'string', 'in:AM,PM'],
        ];
    }
}
