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
        Schema::table('salesforces', function (Blueprint $table) {
            $table->unsignedInteger('withdraw_business_limit')->default(0)->comment('일 출금한도(영업일)');
            $table->unsignedInteger('withdraw_holiday_limit')->default(0)->comment('일 출금한도(휴무일)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
            $table->dropColumn('withdraw_business_limit');
            $table->dropColumn('withdraw_holiday_limit');
        });
    }
};
