<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\APIRequest;

class LoginWithEmailAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', $this->existsRule('users', 'email')],
            'password' => 'required|string|min:6',
        ];
    }
}
