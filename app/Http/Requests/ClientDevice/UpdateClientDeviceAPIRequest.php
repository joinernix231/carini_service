<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;

class UpdateClientDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];


        $rules['status'] = ['required','boolean'];
        $rules['address'] = ['required','string'];
        $rules['latitude'] = ['nullable', 'numeric', 'between:-90,90'];
        $rules['longitude'] = ['nullable', 'numeric', 'between:-180,180'];

        return $rules;
    }
}
