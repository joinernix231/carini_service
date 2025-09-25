<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenancePaymentStatusRule;

class VerifyPaymentMaintenanceAPIRequest extends ApiRequest
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
            new MaintenancePaymentStatusRule($maintenance)
        ];

        return $rules;
    }
}
