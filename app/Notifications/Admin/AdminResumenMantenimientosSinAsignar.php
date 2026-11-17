<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminResumenMantenimientosSinAsignar extends Notification implements ShouldQueue
{
    use Queueable;

    private int $count;
    private array $maintenances;

    public function __construct(int $count, array $maintenances = [])
    {
        $this->count = $count;
        $this->maintenances = $maintenances;
    }

    public function via($notifiable): array
    {
        return ['database', 'expo'];
    }

    public function toArray($notifiable): array
    {
        $body = $this->count > 0
            ? "⚠️ Hay {$this->count} mantenimiento(s) sin asignar que requieren atención"
            : "✅ Todos los mantenimientos están asignados";

        return [
            'title' => '📋 Resumen Semanal - Mantenimientos Sin Asignar',
            'body' => $body,
            'data' => [
                'type' => 'admin_summary_unassigned_maintenances',
                'count' => $this->count,
                'maintenances' => $this->maintenances,
                'screen' => 'MaintenancesList',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => $this->count > 0 ? 'high' : 'normal'
        ];
    }

    public function toExpo($notifiable): array
    {
        $body = $this->count > 0
            ? "⚠️ Hay {$this->count} mantenimiento(s) sin asignar que requieren atención"
            : "✅ Todos los mantenimientos están asignados";

        return [
            'title' => '📋 Resumen Semanal - Mantenimientos Sin Asignar',
            'body' => $body,
            'data' => [
                'type' => 'admin_summary_unassigned_maintenances',
                'count' => $this->count,
                'maintenances' => $this->maintenances,
                'screen' => 'MaintenancesList',
            ],
            'sound' => 'default',
            'badge' => 1,
            'priority' => $this->count > 0 ? 'high' : 'normal'
        ];
    }
}

