<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class CreateTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $rules = Technician::$rules;

        $rules['document'] = ['bail', 'required', 'digits_between:6,20', $this->uniqueRule('technicians', 'document')];
        $rules['name'] = ['bail', 'required', 'string', 'max:255'];

        // User creation requires an email
        $rules['email'] = ['bail', 'required', 'string', 'email', 'max:255', $this->uniqueRule('users', 'email')];
        $rules['phone'] = ['nullable', 'string', 'max:20'];

        return $rules;
    }


}
