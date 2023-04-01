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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('result_code')->default(0)->comment('거래 결과코드');
            $table->foreignId('mcht_id')->comment('가맹점 FK');
            $table->integer('gid')->default(0)->comment('gid');
            $table->string('mid')->default('')->comment('mid');
            $table->string('tid')->default('')->comment('tid');
            $table->datetime('trans_dt')->comment('거래 날짜');
            $table->datetime('trans_tm')->comment('거래 시간');
            $table->integer('trans_price')->comment('거래 금액');
            $table->integer('trans_type')->comment('거래 타입');
            $table->integer('trans_num')->default(0)->comment('거래번호');
            $table->integer('ori_trans_num')->default(0)->comment('원거래번호');
            $table->string('card_nm', 100)->comment('거래 카드명');
            $table->string('card_num', 100)->comment('거래 카드번호');
            $table->integer('bank_cd')->comment('거래 은행코드');
            $table->string('bank_nm')->comment('거래 은행명');
            $table->string('appr_num')->comment('승인번호');
            $table->string('installment')->comment('할부');
            $table->string('buyer_nm')->comment('구매자명');
            $table->string('buyer_phone')->comment('구매자 휴대폰번호');
            $table->string('item_nm')->comment('상품명');
            $table->string('result_msg')->default('')->comment('거래 내용');
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
        Schema::dropIfExists('transactions');
    }
};
