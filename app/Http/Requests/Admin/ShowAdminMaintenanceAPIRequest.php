<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Models\ClientDevice\ClientDevice;

class ShowAdminMaintenanceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }
}
