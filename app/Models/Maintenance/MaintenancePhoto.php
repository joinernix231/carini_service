<?php

namespace App\Models\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class MaintenancePhoto extends Model
{
    protected $table = 'maintenance_photos';

    protected $fillable = [
        'maintenance_id',
        'client_device_id',
        'photo',
        'photo_type',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime'
    ];

    public static array $rules = [
        'maintenance_id' => 'required|exists:maintenances,id',
        'client_device_id' => 'required|exists:client_devices,id',
        'photo' => 'required|string',
        'photo_type' => 'required|in:initial,final,part,other',
        'uploaded_at' => 'required|date'
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
        return $this->belongsTo(ClientDevice::class);
    }

    /**
     * Accesor para obtener la URL completa de la foto.
     * Sigue el patrón del proyecto: guarda solo el nombre y genera la URL completa.
     *
     * @return string|null
     */
    public function getPhotoAttribute(): ?string
    {
        if ($photo = Arr::get($this->attributes, 'photo')) {
            return config('filesystems.disks.s3.url') . 'images/maintenances/' . $photo . '.PNG';
        }
        return null;
    }
}
