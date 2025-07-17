<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;

class ShowMaintenanceAPIRequest extends APIRequest
{
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
}
