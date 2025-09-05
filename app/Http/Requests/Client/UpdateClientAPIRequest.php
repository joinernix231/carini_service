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

        $rules = Client::$rules;
        $rules['identifier'] = ['sometimes', 'string', $this->uniqueRule('clients', 'identifier', $clientId)];;
        $rules['email'] = ['sometimes', 'email', 'max:255', $this->uniqueRule('users', 'email', $clientId)];
        return $rules;
    }
}
