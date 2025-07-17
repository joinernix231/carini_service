<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use App\Rules\DeviceClient\DeviceBelongsToClientRule;

class ShowClientDeviceAPIRequest extends APIRequest
{
    public function validationData()
    {
        $linkDevice = $this->route('linkDevice');

        $this->addParametersToRequest([
            'link_device_id' => $linkDevice->id,
        ]);
        return $this->all();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /**@var Client $client**/
        $client = session('client');

        $rules['link_device_id'] = ['required', 'integer', new DeviceBelongsToClientRule($client)];

        return $rules;
    }
}
