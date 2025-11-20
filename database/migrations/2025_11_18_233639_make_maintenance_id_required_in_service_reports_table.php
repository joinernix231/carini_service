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
            // Primero, actualizar todos los registros existentes que tengan maintenance_id null
            // obteniendo el maintenance_id desde maintenance_client_device
            \DB::statement('
                UPDATE maintenance_service_reports msr
                INNER JOIN maintenance_client_device mcd ON msr.maintenance_client_device_id = mcd.id
                SET msr.maintenance_id = mcd.maintenance_id
                WHERE msr.maintenance_id IS NULL
            ');
            
            // Hacer maintenance_id no nullable nuevamente
            $table->unsignedBigInteger('maintenance_id')->nullable(false)->change();
            
            // Restaurar foreign key constraint
            $table->foreign('maintenance_id')
                ->references('id')
                ->on('maintenances')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_service_reports', function (Blueprint $table) {
            // Eliminar foreign key
            $table->dropForeign(['maintenance_id']);
            
            // Hacer maintenance_id nullable nuevamente
            $table->unsignedBigInteger('maintenance_id')->nullable()->change();
        });
    }
};
