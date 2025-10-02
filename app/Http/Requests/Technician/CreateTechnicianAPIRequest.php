<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class CreateTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {
        $rules = Technician::$rules;

        $rules['document'] = ['bail', 'required', 'digits_between:6,20', $this->uniqueRule('technicians', 'document')];
        $rules['name'] = ['bail', 'required', 'string', 'max:255'];

        // User creation requires an email
        $rules['email'] = ['bail', 'required', 'string', 'email', 'max:255', $this->uniqueRule('users', 'email')];
        $rules['phone'] = ['nullable', 'string', 'max:20'];
        $rules['address'] = ['nullable', 'string', 'max:255'];

        // Nuevos campos del perfil del técnico
        $rules['blood_type'] = ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'];
        $rules['photo'] = ['nullable', 'string', 'max:500'];
        $rules['specialty'] = ['required', 'string', 'max:255'];
        $rules['hire_date'] = ['nullable', 'date', 'before_or_equal:today'];
        $rules['contract_type'] = ['required', 'string', 'in:full_time,part_time,contractor'];
        
        // Documentos
        $rules['eps_pdf'] = ['nullable', 'string', 'max:500'];
        $rules['arl_pdf'] = ['nullable', 'string', 'max:500'];
        $rules['pension_pdf'] = ['nullable', 'string', 'max:500'];

        return $rules;
    }


}
