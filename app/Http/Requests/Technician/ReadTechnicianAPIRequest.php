<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;

class ReadTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

}
