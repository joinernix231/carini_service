<?php

use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\Maintenance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Maintenance::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(ClientDevice::class)->constrained()->onDelete('cascade');
            $table->string('photo');
            $table->enum('photo_type', ['initial', 'final', 'part', 'other'])->default('initial');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_photos');
    }
};
