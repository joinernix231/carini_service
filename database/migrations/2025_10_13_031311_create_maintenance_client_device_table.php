<?php

use App\Models\ClientDevice\ClientDevice;
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
        Schema::create('maintenance_client_device', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Maintenance::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(ClientDevice::class)->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            
            // Campos de progreso agregados posteriormente
            $table->json('progress_completed_indices')->nullable();
            $table->unsignedInteger('progress_completed_count')->nullable();
            $table->unsignedInteger('progress_total')->nullable();
            $table->decimal('progress_pct', 5, 2)->nullable();
            $table->enum('progress_status', ['pending','in_progress','completed'])->nullable();
            $table->timestamp('progress_updated_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_client_device');
    }
};
