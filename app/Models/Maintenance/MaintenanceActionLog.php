<?php

namespace App\Models\Maintenance;

use App\Models\Technician\Technician;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'technician_id',
        'action',
        'timestamp',
        'reason',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Acciones disponibles
    public const ACTIONS = [
        'assign' => 'Asignar',
        'start' => 'Iniciar',
        'pause' => 'Pausar',
        'resume' => 'Reanudar',
        'end' => 'Finalizar',
    ];

    public static array $rules = [
        'maintenance_id' => 'required|integer|exists:maintenances,id',
        'technician_id' => 'required|integer|exists:technicians,id',
        'action' => 'required|in:assign,start,pause,resume,end',
        'timestamp' => 'required|date',
        'reason' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('timestamp', 'desc');
    }

    public function scopeForMaintenance($query, int $maintenanceId)
    {
        return $query->where('maintenance_id', $maintenanceId);
    }

    public function scopeForTechnician($query, int $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function getLocationAttribute(): ?array
    {
        return $this->hasLocation() ? [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ] : null;
    }
}
