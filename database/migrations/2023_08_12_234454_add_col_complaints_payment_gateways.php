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
        Schema::table('complaints', function (Blueprint $table) {
            $table->tinyInteger('complaint_status')->default(0)->comment('민원상태(0=처리전, 1=처리중, 2=처리완료)');
        });
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->boolean('settle_type')->default(0)->comment('정산타입(0=주말제외, 1=주말포함)');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->tinyInteger('under_sales_type')->default(0)->comment('매출미달 적용타입');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
};
