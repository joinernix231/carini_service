<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;

class ShowTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }


}
