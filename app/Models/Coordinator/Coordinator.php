<?php

namespace App\Models\Coordinator;

use App\Models\User;
use Database\Factories\CoordinatorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coordinator extends Model

{
    use SoftDeletes;
    /** @use HasFactory<CoordinatorFactory> */
    use HasFactory;

    protected $table = 'coordinators';

    protected $fillable = [
        'user_id',
        'identification',
        'address',
        'phone',
        'status',
    ];

    public static array $rules = [
        'identification' => 'integer|unique:coordinators,identification',
        'address' => 'required|string',
        'phone' => 'required|integer',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function newFactory(): CoordinatorFactory
    {
        return CoordinatorFactory::new();
    }
}
