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
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'pdf_url',
        'confirmation_required',
        'confirmed_at',
        'confirmation_deadline',
        'coordinator_notified',
        'coordinator_notified_at',
        'coordinator_called',
        'coordinator_called_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'date_maintenance' => 'datetime',
        'spare_parts' => 'array',
        'is_paid' => 'boolean',
        'confirmation_required' => 'boolean',
        'confirmed_at' => 'datetime',
        'confirmation_deadline' => 'datetime',
        'coordinator_notified' => 'boolean',
        'coordinator_notified_at' => 'datetime',
        'coordinator_called' => 'boolean',
        'coordinator_called_at' => 'datetime',
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
     * Relación con el reporte de servicio del mantenimiento.
     */
    public function serviceReport(): HasOne
    {
        return $this->hasOne(MaintenanceServiceReport::class);
    }

    /**
     * Determinar si el mantenimiento está activo basado en el último action log.
     * Un mantenimiento está activo si su último log es: on_the_way, start, pause, o resume
     * (no está finalizado con 'end')
     * 
     * Nota: Este método incluye 'pause' porque un mantenimiento pausado sigue siendo
     * técnicamente "activo" (no finalizado), aunque el técnico no esté trabajando.
     */
    public function isActive(): bool
    {
        $lastLog = $this->getLatestActionLog();
        
        if (!$lastLog) {
            return false;
        }

        // Las acciones activas son las que indican que el mantenimiento no está finalizado
        $activeActions = ['on_the_way', 'start', 'pause', 'resume'];
        
        return in_array($lastLog->action, $activeActions);
    }

    /**
     * Determinar si el mantenimiento está en progreso (técnico trabajando activamente).
     * Un mantenimiento está en progreso si su último log es: on_the_way, start, o resume
     * (excluye 'pause' porque el técnico no está trabajando cuando está pausado)
     */
    public function isInProgress(): bool
    {
        $lastLog = $this->getLatestActionLog();
        
        if (!$lastLog) {
            return false;
        }

        // Las acciones que indican que el técnico está trabajando activamente
        $inProgressActions = ['on_the_way', 'start', 'resume'];
        
        return in_array($lastLog->action, $inProgressActions);
    }

    /**
     * Obtener el último log de acción del mantenimiento.
     * Usa is_last para optimizar la búsqueda
     */
    public function getLatestActionLog(): ?MaintenanceActionLog
    {
        // Primero intentar usar is_last (más eficiente)
        $lastLog = $this->actionLogs()->where('is_last', true)->first();
        
        // Si no hay log con is_last, usar timestamp como fallback
        if (!$lastLog) {
            $lastLog = $this->actionLogs()->latest('timestamp')->first();
        }
        
        return $lastLog;
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
     * Obtener el tiempo total trabajado en el mantenimiento (solo cuando está completado)
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
     * Calcular el tiempo transcurrido trabajado en tiempo real (en milisegundos)
     * Este método calcula el tiempo desde el inicio hasta ahora, restando las pausas.
     * 
     * Fórmula: Tiempo transcurrido = (Ahora - Inicio) - Tiempo pausado total
     * 
     * @return int Tiempo transcurrido en milisegundos
     */
    public function getElapsedWorkTimeMs(): int
    {
        $startLog = $this->actionLogs()->where('action', 'start')->first();
        
        if (!$startLog) {
            return 0;
        }

        // Obtener el último log para determinar el tiempo final
        $lastLog = $this->getLatestActionLog();
        
        // Si el mantenimiento está finalizado, usar el timestamp del end
        // Si no, usar el tiempo actual
        $endTime = ($lastLog && $lastLog->action === 'end') 
            ? $lastLog->timestamp 
            : now();

        // Calcular tiempo total desde el start hasta el tiempo final
        $totalTimeMs = $startLog->timestamp->diffInMilliseconds($endTime);

        // Restar el tiempo pausado total (getTotalPausedTimeMs ya maneja pausas activas)
        $pausedTimeMs = $this->getTotalPausedTimeMs();
        $elapsedTimeMs = $totalTimeMs - $pausedTimeMs;

        // Asegurar que el tiempo no sea negativo
        return max(0, $elapsedTimeMs);
    }

    /**
     * Obtener el tiempo transcurrido trabajado formateado
     * 
     * @return array{total_ms: int, formatted: string, hours: int, minutes: int, seconds: int}
     */
    public function getElapsedWorkTime(): array
    {
        $totalMs = $this->getElapsedWorkTimeMs();

        // Convertir milisegundos a formato legible
        $totalSeconds = floor($totalMs / 1000);
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return [
            'total_ms' => $totalMs,
            'formatted' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
        ];
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
     * Accesor para obtener el último log de acción del mantenimiento.
     * Nota: Este es un accessor, no una relación. Para usarlo, primero carga 'actionLogs':
     * $maintenance->load('actionLogs');
     * $lastLog = $maintenance->last_action_log; // o $maintenance->getLatestActionLog()
     */
    public function getLastActionLogAttribute(): ?MaintenanceActionLog
    {
        return $this->getLatestActionLog();
    }

    /**
     * Sobrescribir el método para que el accessor client sea tratado como una relación cargable.
     */
    public function getRelationValue($key)
    {
        if ($key === 'client') {
            return $this->getClientAttribute();
        }

        if ($key === 'last_action_log') {
            return $this->getLastActionLogAttribute();
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

        if ($key === 'last_action_log') {
            return $this->relationLoaded('actionLogs');
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

    public function getSignaturePhotoAttribute(): ?string
    {
        if ($photo = Arr::get($this->attributes, 'signature_photo')) {
            return config('filesystems.disks.s3.url') . 'images/' . $photo . '.PNG';
        }
        return null;
    }

    /**
     * Accesor para obtener la URL completa del PDF.
     *
     * @return string|null
     */
    public function getPdfUrlAttribute(): ?string
    {
        if ($pdf = Arr::get($this->attributes, 'pdf_url')) {
            return config('filesystems.disks.s3.url') . 'docs/' . $pdf . '.pdf';
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
