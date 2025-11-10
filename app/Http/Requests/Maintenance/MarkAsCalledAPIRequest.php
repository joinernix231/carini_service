<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class MarkAsCalledAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }
}

