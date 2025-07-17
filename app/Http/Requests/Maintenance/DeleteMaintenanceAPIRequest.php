<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMaintenanceAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
