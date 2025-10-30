<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class UploadMultiplePhotosRequest extends APIRequest
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

        return true;
    }

    public function rules(): array
    {
        return [
            'photos' => 'required|array|min:1',
            'photos.*.client_device_id' => ['required', $this->existsRule('client_device')],
            'photos.*.photo' => 'required|string|max:255',
            'photos.*.photo_type' => 'required|in:initial,final,part,other'
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Las fotos son requeridas',
            'photos.array' => 'Las fotos deben ser un array',
            'photos.min' => 'Debe enviar al menos una foto',
            'photos.*.client_device_id.required' => 'El ID del dispositivo del cliente es requerido para cada foto',
            'photos.*.client_device_id.exists' => 'El dispositivo del cliente no existe',
            'photos.*.photo.required' => 'El nombre de la foto es requerido',
            'photos.*.photo.string' => 'El nombre de la foto debe ser texto',
            'photos.*.photo.max' => 'El nombre de la foto no puede exceder 255 caracteres',
            'photos.*.photo_type.required' => 'El tipo de foto es requerido',
            'photos.*.photo_type.in' => 'El tipo de foto debe ser: initial, final, part u other'
        ];
    }
}
