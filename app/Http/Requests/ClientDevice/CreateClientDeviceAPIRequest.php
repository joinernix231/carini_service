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

        $rules = ClientDevice::$rules;

        $rules['address'] = ['required','string'];
        $rules['latitude'] = ['required', 'numeric', 'between:-90,90'];
        $rules['longitude'] = ['required', 'numeric', 'between:-180,180'];
        $rules['device_id'] = ['required', 'integer'];

        return $rules;
    }
}
