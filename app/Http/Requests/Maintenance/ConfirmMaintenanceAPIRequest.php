<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;

class ConfirmMaintenanceAPIRequest extends APIRequest
{
    public function validationData()
    {
        $maintenance = $this->route('maintenance');

        $this->addParametersToRequest([
            'maintenance_id' => $maintenance->id ?? null,
            'maintenance' => $maintenance,
        ]);

        return $this->all();
    }

    public function authorize(): bool
    {
        $allowedRoles = ['cliente'];
        $user = session('user');
        $client = session('client');

        if (!$user || !in_array($user->role, $allowedRoles) || !$client) {
            return false;
        }

        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        // Verificar que el cliente es dueño del mantenimiento
        if (!$maintenance->relationLoaded('clientDevices')) {
            $maintenance->load('clientDevices');
        }

        return $maintenance->clientDevices->isNotEmpty() &&
               $maintenance->clientDevices->contains('client_id', $client->id);
    }

    public function rules(): array
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        return [
            'maintenance' => [
                'required',
                function ($attribute, $value, $fail) use ($maintenance) {
                    if (!$maintenance) {
                        $fail('El mantenimiento no existe.');
                        return;
                    }

                    if ($maintenance->status !== 'assigned') {
                        $fail('Solo se pueden confirmar mantenimientos en estado asignado.');
                    }
                    
                    if (!$maintenance->confirmation_required) {
                        $fail('Este mantenimiento no requiere confirmación.');
                    }
                    
                    if ($maintenance->confirmed_at !== null) {
                        $fail('Este mantenimiento ya fue confirmado.');
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'maintenance.required' => 'El mantenimiento es requerido.',
        ];
    }
}

