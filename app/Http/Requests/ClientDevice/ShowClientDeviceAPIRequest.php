<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;

class ShowClientDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }
}
