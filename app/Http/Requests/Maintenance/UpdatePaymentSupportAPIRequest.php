<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\Concerns\AuthorizesRoles;
use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;

class UpdatePaymentSupportAPIRequest extends APIRequest
{
    use AuthorizesRoles;

    public function authorize(): bool
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        if ($this->userHasRole('administrador')) {
            return true;
        }

        // Solo el cliente dueño del equipo sube el soporte de pago.
        return $this->userHasRole('cliente')
            && $maintenance !== null
            && session('client_id') !== null
            && $maintenance->clientDevices()->where('client_device.client_id', session('client_id'))->exists();
    }

    public function rules(): array
    {
        return [
            'payment_support' => ['required', 'string'],
        ];
    }

}

