<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;

class AdminResetPasswordRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // No se requieren campos adicionales, la identificación viene en la URL
        ];
    }
}

