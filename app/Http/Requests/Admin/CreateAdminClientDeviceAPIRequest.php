<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Models\ClientDevice\ClientDevice;

class CreateAdminClientDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $rules = [];

        $rules = ClientDevice::$rules;

        $rules['device_id'] = ['required', 'integer', 'exists:devices,id'];
        $rules['latitude'] = ['nullable', 'numeric', 'between:-90,90'];
        $rules['longitude'] = ['nullable', 'numeric', 'between:-180,180'];

        return $rules;
    }
}
