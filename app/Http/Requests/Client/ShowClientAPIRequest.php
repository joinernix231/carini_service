<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;
use Illuminate\Foundation\Http\FormRequest;

class ShowClientAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

}
