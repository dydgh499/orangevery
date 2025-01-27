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
        Schema::table('sub_business_registrations', function (Blueprint $table) {
            $table->renameColumn('registration_result', 'registration_code');
            $table->date('registration_dt')->nullable()->comment('가입일');
            $table->date('req_dt')->nullable()->comment('요청일');
            $table->dropColumn('business_num');
            $table->unsignedInteger('mcht_id')->nullable()->comment('가맹점 FK');
        });
        Schema::table('sub_business_registrations', function (Blueprint $table) {
            $table->string('registration_code', 3)->change();
        });
        Schema::table('sub_business_registrations', function (Blueprint $table) {
            $table->foreign('mcht_id', 'mcht_id_fk')
                ->references('id')
                ->on('merchandises')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_business_registrations', function (Blueprint $table) {
            $table->renameColumn('registration_code', 'registration_result');
            $table->dropColumn('registration_dt');
            $table->dropColumn('req_dt');
            $table->string('business_num')->comment('사업자 번호');
            $table->dropColumn('mcht_id');
        });
    }
};
