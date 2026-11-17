<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_action_logs', function (Blueprint $table) {
            // Hacer technician_id nullable (para acciones del coordinador como cancelar)
            $table->foreignId('technician_id')->nullable()->change();
            
            // Cambiar reason de string(255) a text para razones más largas
            $table->text('reason')->nullable()->change();
        });
        
        // Agregar 'cancel' al enum de acciones (MODIFY COLUMN solo existe en MySQL/MariaDB;
        // en SQLite de pruebas se omite)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE maintenance_action_logs MODIFY COLUMN action ENUM('assign', 'on_the_way', 'start', 'pause', 'resume', 'end', 'cancel')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_action_logs', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable(false)->change();
            $table->string('reason', 255)->nullable()->change();
        });
        
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE maintenance_action_logs MODIFY COLUMN action ENUM('assign', 'on_the_way', 'start', 'pause', 'resume', 'end')");
        }
    }
};
