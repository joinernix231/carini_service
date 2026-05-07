<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminResumenMantenimientosCreados extends Notification implements ShouldQueue
{
    use Queueable;

    private int $count;
    private string $period;

    public function __construct(int $count, string $period = 'diario')
    {
        $this->count = $count;
        $this->period = $period;
    }

    public function via($notifiable): array
    {
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        $periodText = $this->period === 'diario' ? 'hoy' : 'esta semana';
        $title = $this->period === 'diario' 
            ? '📊 Resumen Diario - Mantenimientos Creados'
            : '📊 Resumen Semanal - Mantenimientos Creados';

        $body = "Se crearon {$this->count} mantenimiento(s) {$periodText}";

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'admin_summary_maintenances_created',
                'count' => $this->count,
                'period' => $this->period,
                'screen' => 'MaintenancesList',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }

    public function toExpo($notifiable): array
    {
        $periodText = $this->period === 'diario' ? 'hoy' : 'esta semana';
        $title = $this->period === 'diario' 
            ? '📊 Resumen Diario - Mantenimientos Creados'
            : '📊 Resumen Semanal - Mantenimientos Creados';

        $body = "Se crearon {$this->count} mantenimiento(s) {$periodText}";

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'admin_summary_maintenances_created',
                'count' => $this->count,
                'period' => $this->period,
                'screen' => 'MaintenancesList',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => 'normal'
        ];
    }
}

