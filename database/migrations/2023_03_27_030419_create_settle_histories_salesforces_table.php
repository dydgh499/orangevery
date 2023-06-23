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
        Schema::create('settle_histories_salesforces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('sales_id')->nullable()->comment('영업자 FK')->constrained('salesforces')->onDelete('SET NULL');
            $table->string('acct_nm', 50)->nullable()->comment('예금주명');
            $table->string('acct_num', 20)->comment('예금계좌번호');
            $table->string('acct_bank_nm', 30)->comment('은행명');
            $table->string('acct_bank_cd', 3)->comment('은행코드');
            $table->integer('total_amount')->default(0)->comment('매출액');
            $table->integer('cxl_amount')->default(0)->comment('취소액');
            $table->integer('appr_amount')->default(0)->comment('승인액');
            $table->integer('deduct_amount')->default(0)->comment('추가차감액');
            $table->integer('settle_amount')->default(0)->comment('정산액');
            $table->date('settle_dt')->comment('정산일');
            $table->date('deposit_dt')->comment('지급일');
            $table->integer('trans_s_id')->nullable()->comment('시작 매출 ID');
            $table->integer('trans_e_id')->nullable()->comment('종료 매출 ID');
            $table->boolean('status')->default(false)->comment('정산 상태(0=미지급, 1=지급 완료)');
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
        Schema::dropIfExists('settle_histories_salesforces');
    }
};
