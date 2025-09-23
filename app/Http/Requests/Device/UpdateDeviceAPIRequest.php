<?php

namespace App\Http\Requests\Device;

use App\Http\Requests\APIRequest;
use App\Models\Device\Device;

class UpdateDeviceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $device = $this->route('device'); // el objeto device que llega por la ruta

        $rules = Device::$rules;

        $rules['serial'] = ['sometimes', 'string', 'max:100', $this->uniqueRule('devices', 'serial', $device)];
        $rules['model'] = ['sometimes', 'string', 'max:100'];
        $rules['brand'] = ['sometimes', 'string', 'max:100'];
        $rules['description'] = ['sometimes', 'string'];
        $rules['type'] = ['sometimes', 'string', 'max:50'];
        $rules['photo'] = ['sometimes', 'string'];
        $rules['pdf_url'] = ['sometimes', 'nullable', 'string'];

        return $rules;
    }
}
