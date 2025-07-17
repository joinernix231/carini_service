<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;

class UpdateMaintenanceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        $rules['type'] = ['required', 'string'];

        $rules['date_maintenance'] = ['required', 'date'];

        $rules['maintenance_type_id'] = ['required', 'integer'];

        $rules['description'] = ['required', 'string'];

        return $rules;
    }
}
