<?php

namespace App\Models\Device;

use App\Models\ClientDevice\ClientDevice;
use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Device extends Model
{
    use SoftDeletes;
    /** @use HasFactory<DeviceFactory> */
    use HasFactory;


    protected $table = 'devices';

    protected $fillable = [
        'serial',
        'model',
        'brand',
        'description',
        'type',
        'photo',
    ];

    public static array $rules = [
        'serial' => 'required|string|max:100|unique:devices,serial_number',
        'model' => 'nullable|string|max:100',
        'brand' => 'nullable|string|max:100',
        'description' => 'nullable|string',
        'type' => 'nullable|string|max:50',
        'photo' => 'nullable|string',
    ];

    public function clientDevices(): HasMany
    {
        return $this->hasMany(ClientDevice::class);
    }

    protected static function newFactory(): DeviceFactory
    {
        return DeviceFactory::new();
    }

    public function getPhotoAttribute(): ?string
    {
        if ($photo = Arr::get($this->attributes, 'photo'))
            return config('filesystems.disks.s3.url') . 'images/' . $photo . '.PNG';
        return null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        if ($value = Arr::get($this->attributes, 'pdf_url'))
            return config('filesystems.disks.s3.url') . 'docs/' . $value . '.pdf';
        return null;
    }
}
