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
            $table->integer('resale_withdraw_fee')->nullable()->comment('지급이체 수수료(재판매)');
            $table->integer('resale_settle_fee')->nullable()->comment('건별 수수료(재판매)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
            $table->dropColumn('resale_withdraw_fee');
            $table->dropColumn('resale_settle_fee');
        });
    }
};
