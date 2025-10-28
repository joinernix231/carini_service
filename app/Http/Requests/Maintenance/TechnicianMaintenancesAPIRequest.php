<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class TechnicianMaintenancesAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $user = session('user');
        $technician = session('technician');
        
        // Solo técnicos pueden acceder a este endpoint
        return $user && $user->role === 'tecnico' && $technician;
    }

    public function rules(): array
    {
        return [
            'technicanFilters' => 'nullable|string',
            'status' => 'nullable|string|in:assigned,in_progress,completed',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'unpaginated' => 'nullable|boolean'
        ];
    }
}