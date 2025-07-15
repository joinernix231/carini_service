<?php

namespace App\Models\Maintenance;

use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use softDeletes;
    /** @use HasFactory<MaintenanceFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type',
        'date_maintenance',
        ''
    ]
}
