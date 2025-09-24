<?php

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
            $table->decimal('value', 10, 2)->nullable()->after('technician_id');
            $table->json('spare_parts')->nullable()->after('value');
            $table->boolean('is_paid')->nullable()->after('value');
            $table->string('payment_support')->nullable()->after('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('value');
            $table->dropColumn('spare_parts');
            $table->dropColumn('is_paid');
            $table->dropColumn('payment_support');
        });
    }
};
