<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;

class SaveServiceReportRequest extends APIRequest
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
        $rules = \App\Models\Maintenance\MaintenanceServiceReport::$rules;
        // Remover maintenance_id de las reglas ya que viene del route
        unset($rules['maintenance_id']);
        return $rules;
    }

    public function messages(): array
    {
        return [
            'equipment_status.in' => 'El estado del equipo debe ser: FUNCIONANDO, EN_REPARACION, FUERA_DE_SERVICIO u OTRO',
            'spare_parts_quantity.min' => 'La cantidad de repuestos no puede ser negativa',
            'service_amount.min' => 'El monto del servicio no puede ser negativo',
            'expenses_amount_received.min' => 'El monto de viáticos recibidos no puede ser negativo',
            'expenses_amount.min' => 'El monto de viáticos a facturar no puede ser negativo',
        ];
    }
}

