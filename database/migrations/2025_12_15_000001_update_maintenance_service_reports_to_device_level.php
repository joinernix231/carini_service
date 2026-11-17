<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_service_reports', function (Blueprint $table) {
            // Agregar maintenance_client_device_id
            $table->foreignId('maintenance_client_device_id')
                ->nullable()
                ->after('maintenance_id')
                ->constrained('maintenance_client_device')
                ->onDelete('cascade');
            
            // Eliminar campos antiguos de repuestos
            $table->dropColumn(['spare_parts_used', 'spare_parts_quantity']);
            
            // Agregar campo JSON para repuestos usados
            $table->json('used_spare_parts')->nullable()->after('activities_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_service_reports', function (Blueprint $table) {
            // Revertir cambios
            $table->dropForeign(['maintenance_client_device_id']);
            $table->dropColumn('maintenance_client_device_id');
            $table->dropColumn('used_spare_parts');
            
            // Restaurar campos antiguos
            $table->text('spare_parts_used')->nullable();
            $table->integer('spare_parts_quantity')->default(0);
        });
    }
};

