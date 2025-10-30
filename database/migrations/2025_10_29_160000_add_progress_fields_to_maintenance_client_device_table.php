<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_client_device', function (Blueprint $table) {
            $table->json('progress_completed_indices')->nullable()->after('description');
            $table->unsignedInteger('progress_completed_count')->nullable()->after('progress_completed_indices');
            $table->unsignedInteger('progress_total')->nullable()->after('progress_completed_count');
            $table->decimal('progress_pct', 5, 2)->nullable()->after('progress_total');
            $table->enum('progress_status', ['pending','in_progress','completed'])->nullable()->after('progress_pct');
            $table->timestamp('progress_updated_at')->nullable()->after('progress_status');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_client_device', function (Blueprint $table) {
            $table->dropColumn([
                'progress_completed_indices',
                'progress_completed_count',
                'progress_total',
                'progress_pct',
                'progress_status',
                'progress_updated_at',
            ]);
        });
    }
};



