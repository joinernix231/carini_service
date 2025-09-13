<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use Illuminate\Validation\Rule;

class UpdateClientAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client');

        dd($clientId);

        $rules = Client::$rules;
        $rules['identifier'] = ['sometimes', 'string', $this->uniqueRule('clients', 'identifier', $clientId)];
        $rules['name'] = ['sometimes', 'string', 'max:255'];
        $rules['client_type'] = ['sometimes', Rule::in(Client::CLIENT_TYPES)];
        $rules['document_type'] = ['sometimes', Rule::in(Client::DOCUMENT_TYPES)];
        $rules['department'] = ['sometimes', 'string', 'max:255'];

        $rules['email'] = ['sometimes', 'email', 'max:255', $this->uniqueRule('users', 'email', $clientId)];
        $rules['phone'] = ['sometimes', 'string', 'max:20'];

        // Contacts may be provided to sync (add/edit/delete)
        $rules['contacts'] = ['sometimes', 'array'];
        $rules['contacts.*.id'] = ['sometimes', 'integer', 'exists:contacts,id'];
        $rules['contacts.*.nombre_contacto'] = ['required_without:contacts.*.id', 'string', 'max:255'];
        $rules['contacts.*.correo'] = ['nullable', 'email', 'max:255'];
        $rules['contacts.*.telefono'] = ['nullable', 'string', 'max:20'];
        $rules['contacts.*.direccion'] = ['nullable', 'string', 'max:255'];
        $rules['contacts.*.cargo'] = ['nullable', 'string', 'max:255'];

        return $rules;
    }
}
