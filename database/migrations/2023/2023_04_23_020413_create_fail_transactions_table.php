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
        Schema::create('fail_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('pmod_id')->default(0)->comment('pay module ID (장비 ID)');
            $table->integer('pg_id')->default(0)->comment('PG사 id');
            $table->integer('ps_id')->default(0)->comment('구간 id');
            $table->tinyInteger('module_type')->default(0)->comment('거래타입');
            $table->date('trx_dt')->comment('거래 날짜');
            $table->time('trx_tm')->comment('거래 시간');
            $table->integer('amount')->comment('거래 금액');
            $table->boolean('is_cancel')->default(0)->comment('취소 여부');
            $table->string('result_cd', 10)->comment('응답 코드');
            $table->string('result_msg')->comment('응답 메세지');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
