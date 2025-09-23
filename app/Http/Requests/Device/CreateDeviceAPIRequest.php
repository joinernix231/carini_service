<?php

namespace App\Http\Requests\Device;

use App\Http\Requests\APIRequest;
use App\Models\Device\Device;

class CreateDeviceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
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
