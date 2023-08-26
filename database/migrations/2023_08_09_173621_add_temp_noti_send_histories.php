<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('noti_send_histories', function (Blueprint $table) {
            $table->string('temp', 500)->nullable()->comment('임시 값');
        });
        Schema::table('complaints', function (Blueprint $table) {
            $table->tinyInteger('complaint_status')->default(0)->comment('민원상태(0=처리전, 1=처리중, 2=처리완료)');
        });
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->boolean('settle_type')->default(0)->comment('정산타입(0=주말포함, 1=주말제외)');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->tinyInteger('under_sales_type')->default(0)->comment('매출미달 적용타입');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->integer('under_sales_limit')->default(0)->comment('매출미달 상한금액');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('pg_settle_type')->default(0)->comment('PG사 정산타입(0=주말포함, 1=주말제외)');
        });
        Schema::table('operators', function (Blueprint $table) {
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('noti_send_histories', function (Blueprint $table) {
            $table->string('temp');
        });
    }
};
