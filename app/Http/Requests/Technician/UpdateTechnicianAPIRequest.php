<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class UpdateTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $technical = $this->route('technical');

        $rules = Technician::$rules;
        $rules['document'] = ['sometimes', 'string', $this->uniqueRule('technicians', 'document', $technical)];

        $rules['name'] = ['sometimes', 'string', 'max:255'];
        $rules['email'] = ['sometimes', 'email', 'max:255', $this->uniqueRule('users', 'email', $technical->user_id)];
        $rules['phone'] = ['sometimes', 'string', 'max:20'];

        return $rules;
    }


}
