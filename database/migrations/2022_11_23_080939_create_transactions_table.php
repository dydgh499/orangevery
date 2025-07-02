<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');

            $table->integer('pmod_id')->default(0)->comment('pay module ID');
            $table->integer('pg_id')->default(0)->comment('결제대행사 id');
            $table->integer('ps_id')->default(0)->comment('결제대행사 수수료율 id');
            $table->float('ps_fee', 6, 5)->default(0)->comment('결제대행사 수수료율 수수료');
            
            $table->timestamp('trx_at')->index()->nullable()->comment('거래발생시간(취소, 승인)');
            $table->boolean('is_cancel')->default(false)->comment('취소 여부');
            $table->tinyInteger('cxl_seq', false, true)->default(0)->comment('취소 회차');
            $table->integer('amount')->comment('거래 금액');
            $table->tinyInteger('module_type')->comment('모듈 타입(0=장비, 1=수기, 2=인증, 3=간편, 4=빌링)');
            $table->string('ord_num', 100)->default('')->comment('주문번호');
            $table->string('mid', 100)->default('')->comment('MID');
            $table->string('tid', 100)->default('')->comment('TID');
            $table->string('trx_id', 100)->default('')->comment('거래번호');
            $table->string('ori_trx_id', 100)->nullable()->comment('원거래번호');
            $table->string('card_num', 20)->comment('거래 카드번호');
            $table->string('issuer', 20)->nullable()->comment('발급사');
            $table->string('acquirer', 20)->nullable()->comment('매입사');
            $table->string('appr_num', 9)->comment('승인번호');
            $table->tinyInteger('installment')->default(0)->comment('할부');
            $table->timestamps();
            # 여기 밑에 다 삭제 250703
            $table->string('buyer_name', 50)->nullable()->comment('구매자명');
            $table->string('buyer_phone', 20)->nullable()->comment('구매자 휴대폰번호');
            $table->string('item_name', 100)->nullable()->comment('상품명');
            $table->string('issuer_code', 3)->nullable()->comment('발급사 코드');
            $table->string('acquirer_code', 3)->nullable()->comment('매입사 코드');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->unique(['cxl_seq', 'appr_num', 'trx_id'], 'duplicate_unique_key');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['brand_id', 'trx_at'], 'trx_at_index_key');
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
}
