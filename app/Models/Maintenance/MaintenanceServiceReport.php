<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceServiceReport extends Model
{
    use HasFactory;

    protected $table = 'maintenance_service_reports';

    protected $fillable = [
        'maintenance_id',
        'maintenance_client_device_id',
        'version',
        'activities_description',
        'used_spare_parts',
        'equipment_status',
        'bill_service',
        'service_amount',
        'expenses_received',
        'expenses_amount_received',
        'bill_expenses',
        'expenses_amount',
        'food',
        'lodging',
        'transport',
        'client_observations',
    ];

    protected $casts = [
        'used_spare_parts' => 'array',
        'bill_service' => 'boolean',
        'service_amount' => 'decimal:2',
        'expenses_received' => 'boolean',
        'expenses_amount_received' => 'decimal:2',
        'bill_expenses' => 'boolean',
        'expenses_amount' => 'decimal:2',
        'food' => 'boolean',
        'lodging' => 'boolean',
        'transport' => 'boolean',
    ];

    public static array $rules = [
        'maintenance_client_device_id' => 'required|exists:maintenance_client_device,id',
        'version' => 'nullable|string|max:10',
        'activities_description' => 'nullable|string|max:2000',
        'used_spare_parts' => 'nullable|array',
        'used_spare_parts.*.description' => 'required|string|max:500',
        'used_spare_parts.*.quantity' => 'required|integer|min:1',
        'equipment_status' => 'nullable|in:FUNCIONANDO,EN_REPARACION,FUERA_DE_SERVICIO,OTRO',
        'bill_service' => 'nullable|boolean',
        'service_amount' => 'nullable|numeric|min:0',
        'expenses_received' => 'nullable|boolean',
        'expenses_amount_received' => 'nullable|numeric|min:0',
        'bill_expenses' => 'nullable|boolean',
        'expenses_amount' => 'nullable|numeric|min:0',
        'food' => 'nullable|boolean',
        'lodging' => 'nullable|boolean',
        'transport' => 'nullable|boolean',
        'client_observations' => 'nullable|string|max:2000',
    ];

    /**
     * Relación con el dispositivo del mantenimiento (pivot).
     *
     * @return BelongsTo<MaintenanceClientDevice, MaintenanceServiceReport>
     */
    public function maintenanceClientDevice(): BelongsTo
    {
        return $this->belongsTo(MaintenanceClientDevice::class);
    }
}

