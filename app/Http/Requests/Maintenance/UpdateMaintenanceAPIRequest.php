<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;

class UpdateMaintenanceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
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
