<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;
use Illuminate\Foundation\Http\FormRequest;

class ShowClientAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

}
