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
        'version',
        'activities_description',
        'spare_parts_used',
        'spare_parts_quantity',
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
        'spare_parts_quantity' => 'integer',
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
        'maintenance_id' => 'required|exists:maintenances,id',
        'version' => 'nullable|string|max:10',
        'activities_description' => 'nullable|string|max:2000',
        'spare_parts_used' => 'nullable|string|max:1000',
        'spare_parts_quantity' => 'nullable|integer|min:0',
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
     * Relación con el mantenimiento.
     *
     * @return BelongsTo<Maintenance, MaintenanceServiceReport>
     */
    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }
}

