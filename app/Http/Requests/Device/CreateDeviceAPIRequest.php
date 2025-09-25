<?php

namespace App\Http\Requests\Device;

use App\Http\Requests\APIRequest;
use App\Models\Device\Device;

class CreateDeviceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {
        $rules = Device::$rules;

        $rules['model'] = ['required', 'string'];
        $rules['type'] = ['required', 'string'];
        $rules['photo'] = ['required', 'string'];
        $rules['pdf_url'] = ['required', 'string'];

        return $rules;
    }
}
