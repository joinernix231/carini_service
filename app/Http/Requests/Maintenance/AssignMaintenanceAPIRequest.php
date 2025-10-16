<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenancePendingStatusRule;
use App\Rules\Maintenance\TechnicianAvailabilityRule;

class AssignMaintenanceAPIRequest extends ApiRequest
{
    public function validationData(): array
    {
        $maintenance =  $this->route('maintenance');
        $this->addParametersToRequest(['maintenance_id' => $maintenance->id]);

        return $this->all();
    }


    public function authorize(): bool
    {
        $allowedRoles = ['coordinador','administrador'];
        $user = session('user');

        return in_array($user->role, $allowedRoles);
    }


    public function rules(): array
    {
        /**@var Maintenance $maintenance*/
        $maintenance = $this->route('maintenance');

        $rules['maintenance_id'] = ['bail','required', $this->existsRule('maintenances'),
            new MaintenancePendingStatusRule($maintenance)
        ];
        $rules['date_maintenance'] = ['required', 'date_format:Y-m-d'];
        $rules['value'] = ['nullable', 'integer'];
        $rules['shift'] = ['required', 'string', 'in:AM,PM'];
        $rules['technician_id'] = ['required', 'integer', $this->existsRule('technicians'),
            new TechnicianAvailabilityRule($this->input('date_maintenance'), $this->input('shift'))
        ];

        return $rules;
    }
}
