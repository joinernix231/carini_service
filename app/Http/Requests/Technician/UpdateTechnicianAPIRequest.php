<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class UpdateTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return $user && in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {
        $technical = $this->route('technical');

        $rules = Technician::$rules;
        $rules['document'] = ['sometimes', 'string', $this->uniqueRule('technicians', 'document', $technical)];

        $rules['name'] = ['sometimes', 'string', 'max:255'];
        $rules['email'] = ['sometimes', 'email', 'max:255', $this->uniqueRule('users', 'email', $technical->user_id)];
        $rules['phone'] = ['sometimes', 'string', 'max:20'];
        $rules['address'] = ['sometimes', 'string', 'max:255'];

        // Nuevos campos del perfil del técnico
        $rules['blood_type'] = ['sometimes', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'];
        $rules['photo'] = ['sometimes', 'string', 'max:500'];
        $rules['specialty'] = ['sometimes', 'string', 'max:255'];
        $rules['hire_date'] = ['sometimes', 'date', 'before_or_equal:today'];
        $rules['contract_type'] = ['sometimes', 'string', 'in:full_time,part_time,contractor'];
        
        // Documentos
        $rules['eps_pdf'] = ['sometimes', 'string', 'max:500'];
        $rules['arl_pdf'] = ['sometimes', 'string', 'max:500'];
        $rules['pension_pdf'] = ['sometimes', 'string', 'max:500'];

        return $rules;
    }


}
