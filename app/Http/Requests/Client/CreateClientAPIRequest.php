<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use Illuminate\Validation\Rule;

class CreateClientAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $rules = Client::$rules;

        $rules['identifier'] = ['bail', 'required', 'digits_between:6,20', $this->uniqueRule('clients', 'identifier')];
        $rules['name'] = ['bail', 'required', 'string', 'max:255'];
        $rules['client_type'] = ['bail', 'required', Rule::in(Client::CLIENT_TYPES)];
        $rules['document_type'] = ['bail', 'required', Rule::in(Client::DOCUMENT_TYPES)];
        $rules['department'] = ['nullable', 'string', 'max:255'];

        // User creation requires an email
        $rules['email'] = ['bail', 'required', 'string', 'email', 'max:255', $this->uniqueRule('users', 'email')];
        $rules['phone'] = ['nullable', 'string', 'max:20'];

        // Contacts validation - completely optional
        $rules['contacts'] = ['bail', 'nullable', 'array'];
        $rules['contacts.*.nombre_contacto'] = ['required_with:contacts', 'string', 'max:255'];
        $rules['contacts.*.correo'] = ['nullable', 'email', 'max:255'];
        $rules['contacts.*.telefono'] = ['nullable', 'string', 'max:20'];
        $rules['contacts.*.direccion'] = ['nullable', 'string', 'max:255'];
        $rules['contacts.*.cargo'] = ['nullable', 'string', 'max:255'];

        return $rules;
    }
}
