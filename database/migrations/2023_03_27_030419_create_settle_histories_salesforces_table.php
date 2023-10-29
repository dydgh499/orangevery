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
            $table->string('acct_name', 30)->nullable()->comment('예금주명');
            $table->string('acct_num', 20)->nullable()->comment('예금계좌번호');
            $table->string('acct_bank_name', 30)->nullable()->comment('은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->tinyInteger('level')->comment('영업자 레벨');
            $table->integer('total_amount')->default(0)->comment('매출액');
            $table->integer('trx_amount')->default(0)->comment('거래 수수료');            
            $table->integer('cxl_amount')->default(0)->comment('취소액');
            $table->integer('appr_amount')->default(0)->comment('승인액');
            $table->integer('deduct_amount')->default(0)->comment('추가차감액');
            $table->integer('comm_settle_amount')->default(0)->comment('통신비 정산금');
            $table->integer('under_sales_amount')->default(0)->comment('매출미달 차감금');
            $table->integer('settle_amount')->default(0)->comment('정산액');
            $table->date('settle_dt')->comment('정산일');
            $table->date('deposit_dt')->nullable()->comment('입금일');
            $table->boolean('deposit_status')->default(false)->comment('입금 상태(0=미입금, 1=입금완료)');
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
