<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\AuthorizesRoles;

class SaveServiceReportRequest extends APIRequest
{
    use AuthorizesRoles;

    public function authorize(): bool
    {
        $maintenance = $this->route('maintenance');
        $clientDevice = $this->route('clientDevice');

        if (!$clientDevice || !$this->isAssignedTechnician($maintenance)) {
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

