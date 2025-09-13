<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;

class DeleteClientAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
