<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenancePendingForDeletionRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMaintenanceAPIRequest extends APIRequest
{

    public function validationData(): array
    {
        $maintenance = $this->route('maintenance');

        $this->addParametersToRequest([
            'id' => $maintenance->id,
        ]);

        return $this->all();
    }

    public function authorize(): bool
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');
        /** @var Client $client */
        $client = session('client');

        return $maintenance
            && $maintenance->clientDevice
            && $maintenance->clientDevice->client_id === $client->id;
    }

    public function rules(): array
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        return [
            'id' => [
                'required',
                new MaintenancePendingForDeletionRule($maintenance)
            ]
        ];
    }

}
