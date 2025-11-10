<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class TechnicianMaintenancesAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $user = session('user');
        $technician = session('technician');

        return $user && $user->role === 'tecnico' && $technician;
    }
}
