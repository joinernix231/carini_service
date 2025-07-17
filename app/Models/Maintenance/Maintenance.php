<?php

namespace App\Models\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Maintenance extends Model
{
    use softDeletes;
    /** @use HasFactory<MaintenanceFactory> */
    use HasFactory;

    protected $fillable = [
        'client_device_id',
        'type',
        'date_maintenance',
        'maintenance_type_id',
        'photo',
        'description',
        'status',
    ];

    public static array $rules = [
        'client_device_id' => 'required|integer',
        'type' => 'required|string',
        'date_maintenance' => 'required|date',
        'maintenance_type_id' => 'required|string|max:50',
        'photo' => 'nullable|string',
        'description' => 'nullable|string',
    ];

    public function casts(): array
    {
        return [
            'client_device_id' => 'integer',
            'type' => 'string',
            'date_maintenance' => 'date',
            'maintenance_type_id' => 'string',
            'photo' => 'string',
            'description' => 'string',
            'status' => 'string',
        ];
    }

    public function clientDevice(): BelongsTo
    {
        return $this->belongsTo(ClientDevice::class);
    }


    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    public function getPhotoAttribute(): ?string
    {
        if ($photo = Arr::get($this->attributes, 'photo'))
            return config('filesystems.disks.s3.url') . 'images/' . $photo . '.PNG';
        return null;
    }

}
