<?php

use App\Models\ClientDevice\ClientDevice;
use App\Models\Technician\Technician;
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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ClientDevice::class)
                ->constrained()
                ->onDelete('cascade');

            $table->foreignIdFor(Technician::class, 'technician_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->enum('type', ['preventive', 'corrective']);
            $table->date('date_maintenance')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->enum('shift', ['AM', 'PM'])->nullable();
            $table->string('photo')->nullable();
            $table->string('signature_photo')->nullable();
            $table->string('description')->nullable();

            $table->enum('status', [
                'pending',
                'quoted',
                'payment_uploaded',
                'assigned',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->decimal('value', 10, 2)->nullable();
            $table->boolean('is_paid')->nullable();
            $table->string('payment_support')->nullable();
            $table->string('price_support')->nullable();
            $table->string('observations')->nullable();

            $table->json('spare_parts')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
