<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('address');
            $table->string('photo')->nullable()->after('blood_type');

            $table->string('specialty')->after('photo');
            $table->date('hire_date')->nullable()->after('specialty');
            $table->enum('contract_type', ['full_time', 'part_time', 'contractor'])->default('full_time')->after('hire_date');

            $table->string('eps_pdf')->nullable()->after('contract_type');
            $table->string('arl_pdf')->nullable()->after('eps_pdf');
            $table->string('pension_pdf')->nullable()->after('arl_pdf');
        });
    }

    public function down()
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn([
                'blood_type', 'photo', 'specialty', 'hire_date', 'contract_type',
                'eps_pdf', 'arl_pdf', 'pension_pdf'
            ]);
        });
    }
};
