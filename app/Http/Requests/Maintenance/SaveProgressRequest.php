<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class SaveProgressRequest extends APIRequest
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
        return [
            'devices' => 'required|array|min:0',
            'devices.*.client_device_id' => 'required|integer',
            // Permitir ausencia (reset explícito) o array vacío
            'devices.*.completed_indices' => 'nullable|array',
            'devices.*.completed_indices.*' => 'integer',
            'devices.*.items_total' => 'nullable|integer|min:1',
        ];
    }
}


