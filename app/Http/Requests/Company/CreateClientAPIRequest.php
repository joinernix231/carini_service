<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use Illuminate\Validation\Rule;

class CreateCompanyAPIRequest extends APIRequest
{
    public function validationData(): array
    {
        /** @var integer $userId */
        $userId = session('user_id');

        $this->addParametersToRequest([
            'user_id' => $userId,
        ]);

        return $this->all();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = Client::$rules;
        $rules['identifier'] = ['required', 'string', Rule::unique('clients', 'identifier')];
        $rules['user_id'] = ['required', 'integer', Rule::exists('users', 'id')];
        $rules['email'] = ['nullable', 'email', Rule::unique('clients', 'email')];

        return $rules;
    }
}
