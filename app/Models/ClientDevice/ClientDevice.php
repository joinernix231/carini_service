<?php

namespace App\Models\ClientDevice;

use App\Models\Client\Client;
use App\Models\Device\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDevice extends Model
{
    protected $table = 'client_devices';

    protected $fillable = [
        'client_id',
        'device_id',
        'linked_by',
        'linked_at',
        'status',
        'notes',
        'source',
    ];

    public static array $rules = [
        'client_id'  => 'required|exists:clients,id',
        'device_id'  => 'required|exists:devices,id',
        'linked_by'  => 'nullable|exists:users,id',
        'linked_at'  => 'nullable|date',
        'status'     => 'required|in:active,inactive',
        'notes'      => 'nullable|string',
        'source'     => 'nullable|in:qr,manual',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function linkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_by');
    }
}
