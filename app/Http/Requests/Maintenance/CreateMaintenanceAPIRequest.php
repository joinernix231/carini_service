<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\ClientDeviceOwnership;
use App\Rules\Maintenance\UniquePendingMaintenance;

class CreateMaintenanceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['cliente'];
        $user = session('user');

        return  in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {

        $rules = Maintenance::$rules;

        $rules['client_device_id'] = [
            'required',
            'integer', 
            $this->existsRule('client_device'), 
            new ClientDeviceOwnership(),
            new UniquePendingMaintenance()
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'maintenance_type_id.exists' => 'El tipo de mantenimiento seleccionado no existe.',
            'client_device_id.exists' => 'El equipo seleccionado no existe en la base de datos.',
            'client_device_id.required' => 'Debes seleccionar un equipo para el mantenimiento.',
            'client_device_id.integer' => 'El ID del equipo debe ser un número válido.',
        ];
    }

}
