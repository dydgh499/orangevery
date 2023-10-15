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
        Schema::table('settle_histories_merchandises', function (Blueprint $table) {
            $table->integer('comm_settle_amount')->default(0)->comment('통신비 정산금');
            $table->integer('under_sales_amount')->default(0)->comment('매출미달 차감금');
        });
        Schema::table('settle_histories_salesforces', function (Blueprint $table) {
            $table->integer('comm_settle_amount')->default(0)->comment('통신비 정산금');
            $table->integer('under_sales_amount')->default(0)->comment('매출미달 차감금');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->integer('last_settle_month')->length(3)->default(0)->comment('마지막 정산달');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
