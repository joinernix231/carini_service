<?php

namespace App\Http\Requests\Coordinator;

use App\Http\Requests\APIRequest;
use App\Models\Technician\Technician;

class InactiveCoordinator extends APIRequest
{

    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
