<?php

namespace App\Models\Coordinator;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coordinator extends Model


{
    use HasFactory, SoftDeletes;

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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
