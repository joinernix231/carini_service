<?php

namespace App\Models\ClientDevice;

use App\Models\Client\Client;
use App\Models\Device\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDevice extends Model
{

    use SoftDeletes;
    protected $table = 'client_device';

    protected $fillable = [
        'client_id',
        'device_id',
        'serial',
        'linked_by',
        'linked_at',
        'status',
        'address',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public static array $rules = [
        'client_id'  => 'integer|exists:clients,id',
        'device_id'  => 'integer|exists:devices,id',
        'serial'     => 'required|string|max:100|unique:client_device,serial',
        'address'     => 'required|string|regex:/^[a-zA-Z0-9\s\-\.,#]+$/',
        'linked_by'  => 'nullable|exists:users,id',
        'linked_at'  => 'nullable|date',
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
