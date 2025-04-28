<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;

class DeleteClientDeviceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
