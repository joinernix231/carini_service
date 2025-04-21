<?php

namespace App\Models\Device;

use App\Models\ClientDevice\ClientDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $table = 'devices';

    protected $fillable = [
        'serial_number',
        'model',
        'brand',
        'description',
        'status',
        'type',
        'operation_id',
    ];

    public static array $rules = [
        'serial_number' => 'required|string|max:100|unique:devices,serial_number',
        'model' => 'nullable|string|max:100',
        'brand' => 'nullable|string|max:100',
        'description' => 'nullable|string',
        'status' => 'nullable|string|in:active,inactive',
        'type' => 'nullable|string|max:50',
        'operation_id' => 'required|exists:operations,id',
    ];

    public function clientDevices(): HasMany
    {
        return $this->hasMany(ClientDevice::class);
    }
}
