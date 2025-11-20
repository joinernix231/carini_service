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
        $clientDevice = $this->route('clientDevice');
        
        if (!$maintenance || !$clientDevice) {
            return false;
        }

        // Verificar que el técnico es el asignado al mantenimiento
        if ($maintenance->technician_id !== session('technician_id')) {
            return false;
        }

        // Verificar que el dispositivo pertenece al mantenimiento
        $maintenanceClientDevice = \App\Models\Maintenance\MaintenanceClientDevice::where('maintenance_id', $maintenance->id)
            ->where('client_device_id', $clientDevice->id)
            ->exists();

        return $maintenanceClientDevice;
    }

    public function rules(): array
    {
        $rules = \App\Models\Maintenance\MaintenanceServiceReport::$rules;
        // Remover maintenance_client_device_id de las reglas ya que se obtiene del route
        unset($rules['maintenance_client_device_id']);
        return $rules;
    }

    public function messages(): array
    {
        return [
            'equipment_status.in' => 'El estado del equipo debe ser: FUNCIONANDO, EN_REPARACION, FUERA_DE_SERVICIO u OTRO',
            'used_spare_parts.array' => 'Los repuestos usados deben ser un array',
            'used_spare_parts.*.description.required' => 'Cada repuesto debe tener una descripción',
            'used_spare_parts.*.quantity.required' => 'Cada repuesto debe tener una cantidad',
            'used_spare_parts.*.quantity.min' => 'La cantidad de repuestos debe ser al menos 1',
            'service_amount.min' => 'El monto del servicio no puede ser negativo',
            'expenses_amount_received.min' => 'El monto de viáticos recibidos no puede ser negativo',
            'expenses_amount.min' => 'El monto de viáticos a facturar no puede ser negativo',
        ];
    }
}

