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
        Schema::create('spare_part_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onDelete('cascade');
            $table->foreignId('client_device_id')->constrained('client_device')->onDelete('cascade');
            $table->text('description');
            $table->string('photo')->nullable();
            $table->timestamps();
            
            // Índices para optimización
            $table->index('maintenance_id');
            $table->index('client_device_id');
            $table->index(['maintenance_id', 'client_device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_suggestions');
    }
};
