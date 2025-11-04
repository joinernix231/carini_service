<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Rules\Maintenance\MaintenanceDateMatchRule;

class StartMaintenanceRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['tecnico'];
        $user = session('user');

        if (!in_array($user->role, $allowedRoles)) {
            return false;
        }

        $maintenance = $this->route('maintenance');
        if (!$maintenance || $maintenance->technician_id !== session('technician_id')) {
            return false;
        }

        if ($maintenance->status !== 'assigned') {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');

        return [
            'latitude' => ['required', 'numeric', 'between:-90,90', new MaintenanceDateMatchRule($maintenance)],
            'longitude' => 'required|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'La latitud es requerida',
            'longitude.required' => 'La longitud es requerida',
            'latitude.between' => 'La latitud debe estar entre -90 y 90',
            'longitude.between' => 'La longitud debe estar entre -180 y 180',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres'
        ];
    }
}
