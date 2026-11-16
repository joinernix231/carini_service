<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Http\Requests\APIRequest;

class UpdateClientDeviceAPIRequest extends APIRequest
{
    use AuthorizesRoles;


    public function authorize(): bool
    {
        if ($this->userHasRole('administrador')) {
            return true;
        }

        $linkDevice = $this->route('linkDevice');

        return $linkDevice !== null
            && session('client_id') !== null
            && (int) $linkDevice->client_id === (int) session('client_id');
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
