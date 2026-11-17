<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Índices para los filtros y órdenes reales de los listados (A3).
 * Solo aditiva e idempotente: si el índice ya existe o falta una columna, se salta.
 * Sin índices únicos (no puede fallar por datos duplicados).
 */
return new class extends Migration
{
    private array $indexes = [
        'maintenances' => [
            // Listados: where status ... and deleted_at is null order by created_at
            'idx_maintenances_status_deleted_created' => ['status', 'deleted_at', 'created_at'],
            // Agenda del técnico: where technician_id = ? order by date_maintenance
            'idx_maintenances_technician_date' => ['technician_id', 'date_maintenance'],
            // Sin confirmar: status + confirmation_required + confirmed_at
            'idx_maintenances_confirmation' => ['status', 'confirmation_required', 'confirmed_at'],
            'idx_maintenances_date_maintenance' => ['date_maintenance'],
        ],
        'users' => [
            // sendToRole y avisos a administradores/coordinadores
            'idx_users_role' => ['role'],
        ],
        'notifications' => [
            // Listado y conteo de no leídas por usuario
            'idx_notifications_notifiable_read' => ['notifiable_type', 'notifiable_id', 'read_at'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (!Schema::hasColumns($table, $columns) || Schema::hasIndex($table, $name)) {
                    continue;
                }

                try {
                    Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                        $blueprint->index($columns, $name);
                    });
                } catch (\Throwable $e) {
                    Log::warning("No se pudo crear el índice {$name} en {$table}", ['error' => $e->getMessage()]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if (!Schema::hasIndex($table, $name)) {
                    continue;
                }

                try {
                    Schema::table($table, function (Blueprint $blueprint) use ($name) {
                        $blueprint->dropIndex($name);
                    });
                } catch (\Throwable $e) {
                    // p. ej. MySQL no deja borrar un índice que respalda una FK.
                    Log::warning("No se pudo borrar el índice {$name} en {$table}", ['error' => $e->getMessage()]);
                }
            }
        }
    }
};
