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
            // Eliminar la foreign key constraint primero
            $table->dropForeign(['maintenance_id']);
            
            // Hacer maintenance_id nullable ya que ahora se usa maintenance_client_device_id
            $table->unsignedBigInteger('maintenance_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_service_reports', function (Blueprint $table) {
            // Revertir: hacer maintenance_id no nullable
            $table->unsignedBigInteger('maintenance_id')->nullable(false)->change();
            
            // Restaurar foreign key
            $table->foreign('maintenance_id')
                ->references('id')
                ->on('maintenances')
                ->onDelete('cascade');
        });
    }
};
