<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;

class DeleteAdminClientDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        return [];
    }
}
