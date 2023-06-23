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
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('mcht_id')->nullable()->comment('사용 가맹점 ID')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('pg_id')->default(0)->comment('PG사 id');
            $table->integer('ps_id')->default(0)->comment('PG사 구간 id');
            $table->integer('pmod_id')->default(0)->comment('pay module ID (단말기 ID)');
            $table->integer('custom_id')->default(0)->comment('커스텀 필터 ID');
            $table->integer('settle_type')->default(0)->comment('결제조건');
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
