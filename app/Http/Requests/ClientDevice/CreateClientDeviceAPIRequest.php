<?php

namespace App\Http\Requests\ClientDevice;

use App\Http\Requests\APIRequest;
use App\Models\Device\Device;
use App\Models\User;
use App\Rules\DeviceClient\DeviceNotLinkedToClientRule;
use App\Rules\Utils\ObjectBelongsToModelRule;

class CreateClientDeviceAPIRequest extends APIRequest
{
    public function validationData(): array
    {
        /** @var User $user */
        $user = session('user');
        $client = $user->client;

        $this->addParametersToRequest([
            'client_id' => $client->id,
            'source' => 'qr'
        ]);

        return $this->all();
    }

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $rules = [
            'serial' => ['required', 'string', 'max:100', $this->existsRule('devices', 'serial')],
        ];

        $device = Device::query()->where('serial', $this->get('serial'))->first();
        if ($device) {
            $rules['serial'][] = new DeviceNotLinkedToClientRule($device);
        }
        $rules['client_id'] = ['required','integer'];
        $rules['source'] = ['required','string'];
        $rules['address'] = ['nullable','string'];

        return $rules;
    }
}
