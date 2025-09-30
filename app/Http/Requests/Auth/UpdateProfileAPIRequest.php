<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth()->user();
        
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
        ];

        // Reglas específicas según el rol
        switch ($user->role) {
            case 'cliente':
                $rules['document'] = 'sometimes|string|max:20';
                break;
            case 'tecnico':
                $rules['document'] = 'sometimes|string|max:20';
                break;
            case 'coordinador':
                $rules['identification'] = 'sometimes|integer|unique:coordinators,identification,' . ($user->coordinator?->id ?? 'NULL');
                break;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está en uso por otro usuario.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'city.string' => 'La ciudad debe ser una cadena de texto.',
            'city.max' => 'La ciudad no puede tener más de 255 caracteres.',
            'document.string' => 'El documento debe ser una cadena de texto.',
            'document.max' => 'El documento no puede tener más de 20 caracteres.',
            'identification.integer' => 'La identificación debe ser un número entero.',
            'identification.unique' => 'Esta identificación ya está en uso por otro coordinador.',
        ];
    }
}
