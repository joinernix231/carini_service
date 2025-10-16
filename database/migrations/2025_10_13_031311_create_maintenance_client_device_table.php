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
