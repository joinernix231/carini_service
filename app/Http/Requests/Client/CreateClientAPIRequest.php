<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use Illuminate\Validation\Rule;

class CreateClientAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = Client::$rules;
        $rules['identifier'] = ['bail', 'digits_between:6,12',$this->uniqueRule('clients', 'identifier')];
        $rules['email'] = ['bail', 'required', 'string', 'email', 'max:255', $this->uniqueRule('users', 'email')];
        $rules['name'] = ['string', 'max:255'];

        return $rules;
    }
}
