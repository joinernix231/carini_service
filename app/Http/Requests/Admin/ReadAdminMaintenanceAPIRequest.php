<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;

class ReadAdminMaintenanceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }
}

