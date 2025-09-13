<?php

namespace App\Models\Technician;

use App\Models\Maintenance\Maintenance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Model
{

    protected $table = 'technicians';

    protected $fillable = [
        'user_id',
        'document',
        'phone',
        'address',
        'status',
    ];

    public static array $rules = [
        'document' => 'string|max:20',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenances() : hasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}

