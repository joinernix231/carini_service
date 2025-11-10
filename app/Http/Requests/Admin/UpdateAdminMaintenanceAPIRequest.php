<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;

class UpdateAdminMaintenanceAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        $admin = session('user');

        return $admin && $admin->role === 'administrador';
    }

    public function rules(): array
    {
        $rules = [];

        $rules['type'] = ['sometimes', 'string'];
        $rules['date_maintenance'] = ['sometimes', 'date'];
        $rules['shift'] = ['sometimes', 'string', 'in:morning,afternoon,night'];
        $rules['description'] = ['sometimes', 'string'];
        $rules['status'] = ['sometimes', 'string', 'in:pending,assigned,in_progress,completed'];
        $rules['technician_id'] = ['sometimes', 'nullable', 'integer', $this->existsRule('technicians')];
        $rules['value'] = ['sometimes', 'nullable', 'string'];
        $rules['is_paid'] = ['sometimes', 'boolean'];
        $rules['payment_support'] = ['sometimes', 'nullable', 'string'];
        $rules['price_support'] = ['sometimes', 'nullable', 'string'];
        $rules['observations'] = ['sometimes', 'nullable', 'string'];

        return $rules;
    }
}

