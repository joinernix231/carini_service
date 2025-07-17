<?php

namespace App\Http\Requests\Maintenance;



use App\Http\Requests\APIRequest;

class ReadMaintenanceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['cliente'];
        $user = session('user');

        return  in_array($user->role, $allowedRoles);
    }


    public function rules(): array
    {
        return [
            //
        ];
    }
}
