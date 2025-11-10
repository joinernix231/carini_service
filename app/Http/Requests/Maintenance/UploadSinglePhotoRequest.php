<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class UploadSinglePhotoRequest extends APIRequest
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
            'client_device_id' => ['required', $this->existsRule('client_devices')],
            'photo' => 'required|string|max:255',
            'photo_type' => 'required|in:initial,final,part,other'
        ];
    }

    public function messages(): array
    {
        return [
            'client_device_id.required' => 'El ID del dispositivo del cliente es requerido',
            'client_device_id.exists' => 'El dispositivo del cliente no existe',
            'photo_name.required' => 'El nombre de la foto es requerido',
            'photo_name.string' => 'El nombre de la foto debe ser texto',
            'photo_name.max' => 'El nombre de la foto no puede exceder 255 caracteres',
            'photo_type.required' => 'El tipo de foto es requerido',
            'photo_type.in' => 'El tipo de foto debe ser: initial, final, part u other'
        ];
    }
}
