<?php

namespace App\Models\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'client_device_id',
        'description',
        'photo',
    ];

    public static array $rules = [
        'maintenance_id' => 'required|exists:maintenances,id',
        'client_device_id' => 'required|exists:client_device,id',
        'description' => 'required|string|min:10|max:500',
        'photo' => 'nullable|string|max:255',
    ];

    /**
     * Relación con el mantenimiento.
     */
    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    /**
     * Relación con el dispositivo del cliente.
     */
    public function clientDevice(): BelongsTo
    {
        return $this->belongsTo(ClientDevice::class, 'client_device_id');
    }

    /**
     * Scope para obtener sugerencias de un mantenimiento específico.
     */
    public function scopeForMaintenance($query, int $maintenanceId)
    {
        return $query->where('maintenance_id', $maintenanceId);
    }

    /**
     * Scope para obtener sugerencias de un dispositivo específico.
     */
    public function scopeForDevice($query, int $clientDeviceId)
    {
        return $query->where('client_device_id', $clientDeviceId);
    }
}
