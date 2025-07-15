<?php

use App\Models\Device\Device;
use App\Models\Maintenance\MaintenanceType;
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
        Schema::create('maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Device::class)->constrained()->onDelete('cascade');
            $table->enum('type',['preventive', 'corrective']);
            $table->date('date_maintenance');
            $table->foreignIdFor(MaintenanceType::class)->constrained()->onDelete('cascade');
            $table->string('photo');
            $table->string('description');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance');
    }
};
