<?php

namespace App\Models\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

class MaintenanceClientDevice extends Model
{
    protected $table = 'maintenance_client_device';

    protected $fillable = [
        'maintenance_id',
        'client_device_id',
        'description',
        'progress_completed_indices',
        'progress_completed_count',
        'progress_total',
        'progress_pct',
        'progress_status',
        'progress_updated_at',
        'pdf_url',
    ];

    protected $casts = [
        'progress_completed_indices' => 'array',
        'progress_completed_count' => 'integer',
        'progress_total' => 'integer',
        'progress_pct' => 'decimal:2',
        'progress_updated_at' => 'datetime',
    ];

    /**
     * Relación con el mantenimiento.
     *
     * @return BelongsTo<Maintenance, MaintenanceClientDevice>
     */
    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    /**
     * Relación con el dispositivo del cliente.
     *
     * @return BelongsTo<ClientDevice, MaintenanceClientDevice>
     */
    public function clientDevice(): BelongsTo
    {
        return $this->belongsTo(ClientDevice::class);
    }

    /**
     * Relación con el reporte de servicio de este dispositivo.
     *
     * @return HasOne<MaintenanceServiceReport, MaintenanceClientDevice>
     */
    public function serviceReport(): HasOne
    {
        return $this->hasOne(MaintenanceServiceReport::class);
    }

    /**
     * Accesor para obtener la URL completa del PDF.
     *
     * @return string|null
     */
    public function getPdfUrlAttribute(): ?string
    {
        if ($pdf = Arr::get($this->attributes, 'pdf_url')) {
            return config('filesystems.disks.s3.url') . 'docs/' . $pdf . '.pdf';
        }
        return null;
    }
}

