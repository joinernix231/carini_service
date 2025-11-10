<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class PauseMaintenanceRequest extends APIRequest
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

        // Solo se puede pausar si está activo y la última acción es 'start' o 'resume'
        // Cargar action logs para verificar
        $maintenance->load('actionLogs');
        if (!$maintenance->isActive()) {
            return false;
        }

        $lastLog = $maintenance->getLatestActionLog();
        // Solo se puede pausar si la última acción es 'start' o 'resume' (no si ya está pausado)
        if ($lastLog && !in_array($lastLog->action, ['start', 'resume'])) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'pause_reason' => 'nullable|string|max:255',
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
            'pause_reason.max' => 'La razón de pausa no puede exceder 255 caracteres'
        ];
    }
}
