<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\APIRequest;

class RegisterTokenRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'device_id' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'in:ios,android,web'],
            'app_version' => ['sometimes', 'string', 'max:50'],
            'device_model' => ['sometimes', 'string', 'max:100'],
            'os_version' => ['sometimes', 'string', 'max:50']
        ];
    }
}
