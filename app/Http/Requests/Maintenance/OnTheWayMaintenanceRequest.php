<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenanceDateMatchRule;
use App\Rules\Maintenance\MaintenanceStartableRule;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\Exceptions\HttpResponseException;

class OnTheWayMaintenanceRequest extends APIRequest
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
        $allowedRoles = ['tecnico'];
        $user = session('user');

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        return [
            'maintenance_id' => ['bail', 'required', 'integer', $this->existsRule('maintenances'),
                new MaintenanceStartableRule($maintenance),
                ],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'La latitud es requerida',
            'longitude.required' => 'La longitud es requerida',
            'latitude.numeric' => 'La latitud debe ser un número',
            'longitude.numeric' => 'La longitud debe ser un número',
            'latitude.between' => 'La latitud debe estar entre -90 y 90',
            'longitude.between' => 'La longitud debe estar entre -180 y 180',
        ];
    }
}

