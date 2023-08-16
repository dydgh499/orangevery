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
        Schema::table('payment_modules', function (Blueprint $table) {
            //
        });
    }
};
