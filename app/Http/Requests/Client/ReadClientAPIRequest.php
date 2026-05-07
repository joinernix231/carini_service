<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;

class ReadClientAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }


    public function rules(): array
    {
        return [
            //
        ];
    }
}
