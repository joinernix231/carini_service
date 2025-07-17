<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\UniquePendingMaintenance;

class CreateMaintenanceAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = Maintenance::$rules;


        $rules['maintenance_type_id'] = ['exists:maintenance_types,id'];

        $rules['client_device_id'] = ['integer', new UniquePendingMaintenance()];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'maintenance_type_id.exists' => 'El tipo de mantenimiento seleccionado no existe.',
        ];
    }
}
