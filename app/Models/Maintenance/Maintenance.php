<?php

namespace App\Models\Maintenance;

use App\Models\ClientDevice\ClientDevice;
use App\Models\Technician\Technician;
use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

/**
 * Class Maintenance
 *
 * @package App\Models\Maintenance
 *
 * @property int $id
 * @property int $client_device_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $date_maintenance
 * @property string|null $shift
 * @property string|null $photo
 * @property string|null $description
 * @property string $status
 * @property int|null $technician_id
 * @property string|null $value
 * @property bool|null $is_paid
 * @property string|null $payment_support
 * @property array|null $spare_parts
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read ClientDevice $clientDevice
 * @property-read Technician $technician
 */
class Maintenance extends Model
{
    use SoftDeletes, HasFactory;

    /** @use HasFactory<MaintenanceFactory> */

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'client_device_id',
        'type',
        'date_maintenance',
        'shift',
        'photo',
        'description',
        'status',
        'technician_id',
        'value',
        'is_paid',
        'payment_support',
        'spare_parts',
    ];

    /**
     * Reglas de validación del modelo Maintenance.
     *
     * @var array<string, string>
     */
    public static array $rules = [
        'client_device_id' => 'required|integer',
        'type' => 'required|string',
        'date_maintenance' => 'nullable|date',
        'photo' => 'nullable|string',
        'description' => 'nullable|string',
        'status' => 'required|in:pending,assigned,in_progress,completed',
        'technician_id' => 'nullable|integer',
        'value' => 'nullable|string',
        'is_paid' => 'nullable|boolean',
        'payment_support' => 'nullable|string',
        'spare_parts' => 'nullable|json',
    ];

    /**
     * Casts de los atributos del modelo.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'client_device_id' => 'integer',
            'type' => 'string',
            'date_maintenance' => 'date',
            'maintenance_type_id' => 'string',
            'photo' => 'string',
            'description' => 'string',
            'status' => 'string',
            'technician_id' => 'integer',
            'value' => 'string',
            'is_paid' => 'boolean',
            'payment_support' => 'string',
            'spare_parts' => 'json',
        ];
    }

    /**
     * Relación con el dispositivo del cliente.
     *
     * @return BelongsTo<ClientDevice, Maintenance>
     */
    public function clientDevice(): BelongsTo
    {
        return $this->belongsTo(ClientDevice::class);
    }

    /**
     * Relación con el técnico asignado al mantenimiento.
     *
     * @return BelongsTo<Technician, Maintenance>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class)->with('user');
    }

    /**
     * Accesor para obtener la URL completa de la foto.
     *
     * @return string|null
     */
    public function getPhotoAttribute(): ?string
    {
        if ($photo = Arr::get($this->attributes, 'photo')) {
            return config('filesystems.disks.s3.url') . 'images/' . $photo . '.PNG';
        }
        return null;
    }

    /**
     * Accesor para obtener la URL completa del soporte de pago.
     *
     * @return string|null
     */
    public function getPaymentSupportAttribute(): ?string
    {
        if ($paymentSupport = Arr::get($this->attributes, 'paymentSupport')) {
            return config('filesystems.disks.s3.url') . 'docs/pdfs/' . $paymentSupport;
        }
        return null;
    }
}
