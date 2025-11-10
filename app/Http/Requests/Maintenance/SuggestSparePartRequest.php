<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use Illuminate\Validation\Rule;

class SuggestSparePartRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['tecnico'];
        $user = session('user');

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return false;
        }

        $maintenance = $this->route('maintenance');
        return $maintenance && $maintenance->technician_id === session('technician_id');
    }

    public function rules(): array
    {
        $maintenanceId = $this->route('maintenance')->id ?? null;

        return [
            'description' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'client_device_id' => [
                'required',
                'integer',
                Rule::exists('maintenance_client_device', 'client_device_id')
                    ->where('maintenance_id', $maintenanceId),
            ],
            'photo' => [
                'nullable',
                'string',
                'max:255',
                ],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'La descripción del repuesto es requerida.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'description.max' => 'La descripción no puede exceder 500 caracteres.',
            'client_device_id.required' => 'Debes seleccionar un equipo.',
            'client_device_id.exists' => 'El equipo seleccionado no está asociado a este mantenimiento.',
            'photo.regex' => 'El nombre de la foto no es válido.',
        ];
    }
}
