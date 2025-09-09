<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Device\Device;
use App\Models\User;
use App\Rules\DeviceClient\DeviceNotLinkedToClientRule;
use App\Rules\Utils\ObjectBelongsToModelRule;

class CreateClientDeviceAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return session()->has('client');
    }
    public function rules(): array
    {
        $rules = [];

        $rules = ClientDevice::$rules;

        return $rules;
    }
}
