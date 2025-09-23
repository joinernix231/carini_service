<?php

namespace App\Http\Requests\Device;

use App\Http\Requests\APIRequest;
use App\Rules\Device\ModelHasObjectsRule;

class DeleteDeviceAPIRequest extends APIRequest
{

    public function validationData(): array
    {
        $this->addParametersToRequest([
            'id' => $this->route('device')->id
            ]);
        return $this->all();
    }

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $rules = [];

        $device = $this->route('device');

        $rules['id'] = ['bail', 'required', 'integer', $this->existsRule('devices'),
            new ModelHasObjectsRule($device, 'clientDevices', 'clientes')];

        return $rules;
    }

}
