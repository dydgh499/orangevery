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
        Schema::table('merchandises', function (Blueprint $table) {
            $table->tinyInteger('business_type')->default(1)->comment('사업자유형(0=개인사업자, 1=법인, 2=비사업자)');
            $table->string('corp_registration_num', 20)->nullable()->comment('법인등록번호');
        });
        Schema::table('salesforces', function (Blueprint $table) {
            $table->tinyInteger('business_type')->default(1)->comment('사업자유형(0=개인사업자, 1=법인, 2=비사업자)');
            $table->string('corp_registration_num', 20)->nullable()->comment('법인등록번호');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('business_type');
            $table->dropColumn('corp_registration_num');
        });
        Schema::table('salesforces', function (Blueprint $table) {
            $table->dropColumn('business_type');
            $table->dropColumn('corp_registration_num');
        });
    }
};
