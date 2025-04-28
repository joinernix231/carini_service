<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;

class ReadClientDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }

}
