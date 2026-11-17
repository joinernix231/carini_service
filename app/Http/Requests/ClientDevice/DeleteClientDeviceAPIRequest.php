<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Http\Requests\APIRequest;

class DeleteClientDeviceAPIRequest extends APIRequest
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
}
