<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class TechnicianMaintenancesListRequest extends APIRequest
{
    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = auth()->user();

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:pending,assigned,in_progress,completed',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ];
    }
}

