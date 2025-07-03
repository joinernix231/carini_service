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
        $rules = Client::$rules;
        $rules['identifier'] = ['required', 'string', Rule::unique('clients', 'identifier')];
        $rules['email'] = ['nullable', 'email', Rule::unique('clients', 'email')];

        return $rules;
    }
}
