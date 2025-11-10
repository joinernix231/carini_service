<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class ShowTechnicianMaintenanceRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['tecnico'];
        $user = session('user');

        if (!in_array($user->role, $allowedRoles)) {
            return false;
        }

        $maintenance = $this->route('maintenance');
        if (!$maintenance || $maintenance->technician_id !== session('technician_id')) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
