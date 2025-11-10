<?php

use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_action_logs', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignIdFor(Maintenance::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Technician::class)->constrained()->onDelete('cascade');

            // Acción realizada
            $table->enum('action', ['assign', 'on_the_way', 'start', 'pause', 'resume', 'end']);

            // Timestamp del evento
            $table->timestamp('timestamp');

            // Razón del cambio (opcional)
            $table->string('reason')->nullable();

            // Coordenadas GPS (opcional)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Campo agregado posteriormente
            $table->boolean('is_last')->default(false);

            $table->timestamps();

            // Índices optimizados
            $table->index(['maintenance_id', 'timestamp']);
            $table->index(['technician_id', 'timestamp']);
            $table->index(['action', 'timestamp']);
            $table->index(['maintenance_id', 'is_last'], 'idx_maintenance_is_last');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_action_logs');
    }
};
