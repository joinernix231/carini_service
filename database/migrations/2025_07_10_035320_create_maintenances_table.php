<?php

use App\Models\ClientDevice\ClientDevice;
use App\Models\Device\Device;
use App\Models\Maintenance\MaintenanceType;
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

            $table->foreignIdFor(ClientDevice::class)->constrained()->onDelete('cascade');
            $table->enum('type', ['preventive', 'corrective']);
            $table->date('date_maintenance')->nullable();
            $table->enum('shift', ['AM', 'PM'])->nullable();


            $table->string('photo')->nullable();
            $table->string('description')->nullable();

            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed'])->default('pending');
            $table->foreignIdFor(Technician::class, 'technician_id')->nullable()->constrained()->onDelete('set null');


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
