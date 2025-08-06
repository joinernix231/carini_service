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
    use SoftDeletes;

    protected $table = 'technicians';

    protected $fillable = [
        'user_id ',
        'document ',
        'phone',
        'address',
        'status',
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

