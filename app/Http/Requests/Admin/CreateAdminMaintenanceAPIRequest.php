<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\DevicesWithinRadiusRule;
use App\Rules\Maintenance\UniquePendingMaintenanceForDevicesRule;

class CreateAdminMaintenanceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $rules = Maintenance::$rules;

        $rules['client_devices'] = [
            'required',
            'array',
            'min:1',
            new UniquePendingMaintenanceForDevicesRule(),
            new DevicesWithinRadiusRule()
        ];

        $rules['client_devices.*.id'] = [
            'required',
            'integer',
            $this->existsRule('client_device'),
        ];

        $rules['client_devices.*.description'] = [
            'nullable',
            'string',
            'max:1000'
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'client_devices.required' => 'Debes seleccionar al menos un equipo para el mantenimiento.',
            'client_devices.array' => 'Los equipos deben ser enviados como una lista.',
            'client_devices.min' => 'Debes seleccionar al menos un equipo para el mantenimiento.',
            'client_devices.*.id.required' => 'El ID del equipo es requerido.',
            'client_devices.*.id.integer' => 'El ID del equipo debe ser un número válido.',
            'client_devices.*.id.exists' => 'El equipo seleccionado no existe en la base de datos.',
            'client_devices.*.description.string' => 'La descripción del equipo debe ser texto.',
            'client_devices.*.description.max' => 'La descripción del equipo no puede exceder 1000 caracteres.',
        ];
    }
}

