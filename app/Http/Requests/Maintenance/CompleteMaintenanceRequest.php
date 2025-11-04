<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class CompleteMaintenanceRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['tecnico'];
        $user = session('user');

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return false;
        }

        $maintenance = $this->route('maintenance');
        if (!$maintenance || $maintenance->technician_id !== session('technician_id')) {
            return false;
        }

        // Solo se puede completar si está en progreso
        if ($maintenance->status !== 'in_progress') {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'final_observations' => 'nullable|string|max:1000',
            'client_signature' => 'nullable|string|max:255',
            'final_photos' => 'nullable|array',
            'final_photos.*.client_device_id' => 'required|integer|exists:client_device,id',
            'final_photos.*.photo' => 'required|string|max:255',
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
            'final_observations.max' => 'Las observaciones finales no pueden exceder 1000 caracteres',
            'client_signature.max' => 'La firma del cliente no puede exceder 255 caracteres',
            'final_photos.*.client_device_id.required' => 'El ID del dispositivo del cliente es requerido para cada foto final',
            'final_photos.*.client_device_id.exists' => 'El dispositivo del cliente no existe',
            'final_photos.*.photo.required' => 'El nombre de la foto final es requerido',
            'final_photos.*.photo.string' => 'El nombre de la foto final debe ser texto',
            'final_photos.*.photo.max' => 'El nombre de la foto final no puede exceder 255 caracteres',
        ];
    }
}

