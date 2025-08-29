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
        $rules['identifier'] = ['required', 'integer', $this->uniqueRule('clients', 'identifier')];;
        $rules['email'] = ['required', 'string', 'email', 'max:255', $this->uniqueRule('users', 'email')];
        return $rules;
    }
}
