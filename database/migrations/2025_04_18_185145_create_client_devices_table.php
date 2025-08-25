<?php

use App\Models\Client\Client;
use App\Models\Device\Device;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('client_device', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Client::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Device::class)->constrained()->onDelete('cascade');
            $table->string('serial')->unique();
            $table->foreignIdFor(User::class, 'linked_by')->nullable()->constrained()->onDelete('set null');

            $table->boolean('status')->default(true);
            $table->string('address')->nullable();
            $table->string('source')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_devices');
    }
};
