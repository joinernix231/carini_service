<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'client_type')) {
                $table->enum('client_type', ['Natural', 'Jurídico'])->nullable()->after('phone');
            }
            if (!Schema::hasColumn('clients', 'document_type')) {
                // Include NIT to align with provided example payload
                $table->enum('document_type', ['CC', 'CE', 'CI', 'PASS', 'NIT'])->nullable()->after('client_type');
            }
            if (!Schema::hasColumn('clients', 'department')) {
                $table->string('department')->nullable()->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'document_type')) {
                $table->dropColumn('document_type');
            }
            if (Schema::hasColumn('clients', 'client_type')) {
                $table->dropColumn('client_type');
            }
            if (Schema::hasColumn('clients', 'department')) {
                $table->dropColumn('department');
            }
        });
    }
};
