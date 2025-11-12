<?php

namespace App\Models\ClientDevice;

use App\Models\Client\Client;
use App\Models\Device\Device;
use App\Models\Maintenance\Maintenance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDevice extends Model
{

    use HasFactory, SoftDeletes;
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
        'serial'     => 'string|max:100|unique:client_device,serial',
        'address'    => 'string',
        'latitude'   => 'nullable|numeric|between:-90,90',
        'longitude'  => 'nullable|numeric|between:-180,180',
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

    /**
     * Relación con los mantenimientos (muchos a muchos).
     *
     * @return BelongsToMany<Maintenance, ClientDevice>
     */
    public function maintenances(): BelongsToMany
    {
        return $this->belongsToMany(Maintenance::class, 'maintenance_client_device')
            ->withPivot('description')
            ->withTimestamps();
    }
}
