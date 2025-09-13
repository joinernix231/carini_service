<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;

class ReadClientAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }


    public function rules(): array
    {
        return [
            //
        ];
    }
}
