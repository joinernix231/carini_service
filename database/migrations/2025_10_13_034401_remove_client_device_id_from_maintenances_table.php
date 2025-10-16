<?php

use App\Models\ClientDevice\ClientDevice;
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
        Schema::table('maintenances', function (Blueprint $table) {
            // Verificar si la columna existe antes de eliminarla
            if (Schema::hasColumn('maintenances', 'client_device_id')) {
                // Eliminar la foreign key constraint primero si existe
                try {
                    $table->dropForeign(['client_device_id']);
                } catch (\Exception $e) {
                    // La foreign key ya no existe, continuar
                }
                // Eliminar la columna
                $table->dropColumn('client_device_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Restaurar la columna
            $table->foreignIdFor(ClientDevice::class)->constrained()->onDelete('cascade');
        });
    }
};
