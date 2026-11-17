<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenancePendingForDeletionRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMaintenanceAPIRequest extends APIRequest
{
    use AuthorizesRoles;


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

        // Solo un cliente puede borrar su solicitud (antes un no-cliente provocaba un 500).
        if (!$maintenance || !$client || !$this->userHasRole('cliente')) {
            return false;
        }

        // Cargar la relación clientDevices si no está cargada
        if (!$maintenance->relationLoaded('clientDevices')) {
            $maintenance->load('clientDevices');
        }

        // Verificar que el mantenimiento tenga dispositivos y que al menos uno pertenezca al cliente
        return $maintenance
            && $maintenance->clientDevices->isNotEmpty()
            && $maintenance->clientDevices->contains('client_id', $client->id);
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
