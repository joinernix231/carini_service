<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenancePendingForDeletionRule;

class DeleteAdminMaintenanceAPIRequest extends APIRequest
{
    public function validationData(): array
    {
        $maintenance = $this->route('maintenance');

        $this->addParametersToRequest([
            'id' => $maintenance->id,
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
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        return [
            'id' => [
                'required',
                new MaintenancePendingForDeletionRule($maintenance)
            ]
        ];
    }
}

