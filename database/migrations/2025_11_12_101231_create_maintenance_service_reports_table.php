<?php

use App\Models\Maintenance\Maintenance;
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
        Schema::create('maintenance_service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Maintenance::class)->constrained()->onDelete('cascade');
            
            // Versión del formulario
            $table->string('version')->default('1.0');
            
            // Descripción de actividades
            $table->text('activities_description')->nullable();
            
            // Repuestos utilizados
            $table->text('spare_parts_used')->nullable();
            $table->integer('spare_parts_quantity')->default(0);
            
            // Estado del equipo
            $table->enum('equipment_status', [
                'FUNCIONANDO',
                'EN_REPARACION',
                'FUERA_DE_SERVICIO',
                'OTRO'
            ])->nullable();
            
            // Facturación del servicio
            $table->boolean('bill_service')->default(false);
            $table->decimal('service_amount', 10, 2)->nullable();
            
            // Viáticos recibidos
            $table->boolean('expenses_received')->default(false);
            $table->decimal('expenses_amount_received', 10, 2)->nullable();
            
            // Facturar viáticos
            $table->boolean('bill_expenses')->default(false);
            $table->decimal('expenses_amount', 10, 2)->nullable();
            
            // Otros gastos
            $table->boolean('food')->default(false);
            $table->boolean('lodging')->default(false);
            $table->boolean('transport')->default(false);
            
            // Observaciones del cliente
            $table->text('client_observations')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_service_reports');
    }
};
