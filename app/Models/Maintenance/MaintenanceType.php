<?php

namespace App\Models\Maintenance;

use Database\Factories\MaintenanceTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceType extends Model
{
    use softDeletes;
    /** @use HasFactory<MaintenanceTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type',
        'date_maintenance',
        'maintenance_type_id',
        'photo',
        'description',
    ];

    public function casts(): array
    {
        return [
            'name' => 'string',
            'value' => 'string',
        ];
    }

    public static array $rules = [
        'name' => 'required|string',
        'value' => 'required|string',
    ];

    protected static function newFactory(): MaintenanceTypeFactory
    {
        return MaintenanceTypeFactory::new();
    }


}
