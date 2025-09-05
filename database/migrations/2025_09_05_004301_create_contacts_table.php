<?php

use App\Models\Client\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class, 'client_id')
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->string('nombre_contacto');
            $table->string('correo')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('direccion')->nullable();
            $table->string('cargo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
