<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('technician_id')
                ->nullable()
                ->after('status')
                ->constrained('technicians')
                ->onDelete('set null');
            Schema::table('maintenances', function (Blueprint $table) {
                $table->enum('shift', ['AM', 'PM'])->nullable()->after('date_maintenance');
            });

        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
            $table->dropColumn('shift');
        });
    }
};
