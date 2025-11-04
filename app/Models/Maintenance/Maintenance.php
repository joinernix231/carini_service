<?php

namespace App\Models\Maintenance;

use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\MaintenancePhoto;
use App\Models\Maintenance\MaintenanceActionLog;
use App\Models\Maintenance\SparePartSuggestion;
use App\Models\Technician\Technician;
use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ClientDevice> $clientDevices
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
        'started_at',
        'observations',
        'signature_photo',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'date_maintenance' => 'datetime',
        'spare_parts' => 'array',
        'is_paid' => 'boolean',
    ];

    /**
     * Reglas de validación del modelo Maintenance.
     *
     * @var array<string, string>
     */
    public static array $rules = [
        'type' => 'required|string',
        'date_maintenance' => 'nullable|date',
        'photo' => 'nullable|string',
        'description' => 'nullable|string',
        'status' => 'in:pending,assigned,in_progress,completed',
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
     * Relación con los dispositivos del cliente (muchos a muchos).
     *
     * @return BelongsToMany<ClientDevice, Maintenance>
     */
    public function clientDevices(): BelongsToMany
    {
        return $this->belongsToMany(ClientDevice::class, 'maintenance_client_device')
            ->withPivot(
                'description',
                'progress_completed_indices',
                'progress_completed_count',
                'progress_total',
                'progress_pct',
                'progress_status',
                'progress_updated_at'
            )
            ->withTimestamps();
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
     * Relación con las fotos del mantenimiento.
     *
     * @return HasMany<MaintenancePhoto, Maintenance>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(MaintenancePhoto::class);
    }

    /**
     * Relación con los logs de acción del mantenimiento.
     */
    public function actionLogs(): HasMany
    {
        return $this->hasMany(MaintenanceActionLog::class);
    }

    /**
     * Relación con las sugerencias de repuesto del mantenimiento.
     */
    public function sparePartSuggestions(): HasMany
    {
        return $this->hasMany(SparePartSuggestion::class);
    }

    /**
     * Obtener el último log de acción del mantenimiento.
     */
    public function getLatestActionLog(): ?MaintenanceActionLog
    {
        return $this->actionLogs()->latest('timestamp')->first();
    }

    /**
     * Obtener la última ubicación GPS del mantenimiento.
     */
    public function getLatestLocation(): ?array
    {
        $latestActionLog = $this->getLatestActionLog();
        
        return $latestActionLog && $latestActionLog->hasLocation() ? $latestActionLog->location : null;
    }

    /**
     * Obtener logs de acción por tipo específico
     */
    public function getActionLogsByAction(string $action)
    {
        return $this->actionLogs()->where('action', $action)->get();
    }

    /**
     * Verificar si el mantenimiento tiene una acción específica
     */
    public function hasAction(string $action): bool
    {
        return $this->actionLogs()->where('action', $action)->exists();
    }

    /**
     * Obtener el tiempo total trabajado en el mantenimiento
     */
    public function getTotalWorkTime(): ?string
    {
        $startLog = $this->actionLogs()->where('action', 'start')->first();
        $endLog = $this->actionLogs()->where('action', 'end')->first();

        if (!$startLog || !$endLog) {
            return null;
        }

        $duration = $startLog->timestamp->diff($endLog->timestamp);
        return $duration->format('%H:%I:%S');
    }

    /**
     * Calcular el tiempo total pausado acumulado en milisegundos
     * Optimizado para no cargar relaciones innecesarias
     */
    public function getTotalPausedTimeMs(): int
    {
        // Solo obtener logs de pause/resume, ordenados
        $logs = $this->actionLogs()
            ->select('action', 'timestamp')
            ->whereIn('action', ['pause', 'resume'])
            ->orderBy('timestamp')
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        $totalPausedMs = 0;
        $pauseTimestamp = null;

        foreach ($logs as $log) {
            if ($log->action === 'pause') {
                $pauseTimestamp = $log->timestamp;
            } elseif ($log->action === 'resume' && $pauseTimestamp) {
                // Sumar duración en milisegundos
                $totalPausedMs += $pauseTimestamp->diffInMilliseconds($log->timestamp);
                $pauseTimestamp = null;
            }
        }

        // Si hay un pause sin resume (mantenimiento actualmente pausado)
        // Sumar desde el pause hasta ahora
        if ($pauseTimestamp) {
            $totalPausedMs += $pauseTimestamp->diffInMilliseconds(now());
        }

        return $totalPausedMs;
    }

    /**
     * Obtener el historial completo de acciones
     */
    public function getActionHistory()
    {
        return $this->actionLogs()
            ->with('technician')
            ->orderBy('timestamp')
            ->get();
    }

    /**
     * Accesor para obtener el cliente asociado al mantenimiento.
     * Este accessor obtiene el cliente del primer ClientDevice asociado.
     *
     * @return Client|null
     */
    public function getClientAttribute(): ?Client
    {
        if ($this->relationLoaded('clientDevices') && $this->clientDevices->isNotEmpty()) {
            return $this->clientDevices->first()->client;
        }

        // Si no están cargadas las relaciones, cargarlas automáticamente
        if (!$this->relationLoaded('clientDevices')) {
            $this->load('clientDevices.client');
            if ($this->clientDevices->isNotEmpty()) {
                return $this->clientDevices->first()->client;
            }
        }

        return null;
    }

    /**
     * Sobrescribir el método para que el accessor client sea tratado como una relación cargable.
     */
    public function getRelationValue($key)
    {
        if ($key === 'client') {
            return $this->getClientAttribute();
        }

        return parent::getRelationValue($key);
    }

    /**
     * Sobrescribir el método para que Laravel reconozca client como una relación cargada.
     */
    public function relationLoaded($key)
    {
        if ($key === 'client') {
            return $this->relationLoaded('clientDevices');
        }

        return parent::relationLoaded($key);
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
        if ($paymentSupport = Arr::get($this->attributes, 'payment_support')) {
            return config('filesystems.disks.s3.url') . 'docs/pdfs/' . $paymentSupport;
        }
        return null;
    }

    public function getPriceSupportAttribute(): ?string
    {
        if ($paymentSupport = Arr::get($this->attributes, 'price_support')) {
            return config('filesystems.disks.s3.url') . 'docs/pdfs/' . $paymentSupport;
        }
        return null;
    }
}
