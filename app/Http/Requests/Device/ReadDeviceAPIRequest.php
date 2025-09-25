<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class ReadDeviceAPIRequest extends FormRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

}
