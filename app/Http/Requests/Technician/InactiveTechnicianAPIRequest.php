<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class InactiveTechnicianAPIRequest extends APIRequest
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
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
