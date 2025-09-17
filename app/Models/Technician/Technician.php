<?php

namespace App\Models\Technician;

use App\Models\Maintenance\Maintenance;
use App\Models\User;
use Database\Factories\TechnicalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{

    /** @use HasFactory<TechnicalFactory> */
    use HasFactory;

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

    protected static function newFactory(): TechnicalFactory
    {
        return TechnicalFactory::new();
    }
}

