<?php

namespace App\Models\Coordinator;

use Illuminate\Database\Eloquent\Model;

class Coordinator extends Model
{
    protected $table = 'coordinators';

    protected $fillable = [
        'identification',
        'address',
        'phone',
        'status',
    ];

    public static array $rules = [
        'identification' => 'integer|unique:coordinators,identification',
        'address' => 'required|string',
        'phone' => 'required|integer',
        'status' => 'required|boolean',
    ];
}
