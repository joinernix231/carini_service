<?php

namespace App\Http\Requests\Device;

use App\Http\Requests\APIRequest;

class DeleteDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

}
