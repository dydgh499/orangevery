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
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('pmod_id')->default(0)->comment('pay module ID (장비 ID)');
            
            $table->integer('brand_settle_amount')->default(0)->comment('본사 정산금');
            $table->float('dev_realtime_fee', 6, 5)->default(0)->comment('개발사 실시간 수수료');
            $table->integer('dev_realtime_settle_amount')->default(0)->comment('개발사 실시간 정산금');
            $table->integer('dev_settle_amount')->default(0)->comment('개발사 정산금');
            $table->float('dev_fee', 6, 5)->default(0)->comment('개발사 거래 수수료');
            $table->integer('dev_settle_id')->nullable()->comment('개발사 정산 ID');
            //
            $table->integer('sales5_settle_amount')->default(0)->comment('하위대리점 정산금');
            $table->unsignedMediumInteger('sales5_id')->nullable()->comment('지사');
            $table->float('sales5_fee', 6, 5)->default(0)->comment('지사 수수료');
            $table->unsignedInteger('sales5_settle_id')->nullable()->comment('지사 정산 ID');
            //
            $table->integer('sales4_settle_amount')->default(0)->comment('대리점 정산금');
            $table->unsignedMediumInteger('sales4_id')->nullable()->comment('하위 지사');
            $table->float('sales4_fee', 6, 5)->default(0)->comment('하위 지사 수수료');
            $table->unsignedInteger('sales4_settle_id')->nullable()->comment('하위 지사 정산 ID');
            //
            $table->integer('sales3_settle_amount')->default(0)->comment('하위총판 정산금');
            $table->unsignedMediumInteger('sales3_id')->nullable()->comment('총판');
            $table->float('sales3_fee', 6, 5)->default(0)->comment('총판 수수료');
            $table->unsignedInteger('sales3_settle_id')->nullable()->comment('총판 정산 ID');
            //
            $table->integer('sales2_settle_amount')->default(0)->comment('총판 정산금');
            $table->unsignedMediumInteger('sales2_id')->nullable()->comment('하위 총판');
            $table->float('sales2_fee', 6, 5)->default(0)->comment('하위 총판 수수료');
            $table->unsignedInteger('sales2_settle_id')->nullable()->comment('하위 총판 정산 ID');
            //
            $table->integer('sales1_settle_amount')->default(0)->comment('하위 지사 정산금');
            $table->integer('sales1_id')->nullable()->comment('대리점');
            $table->float('sales1_fee', 6, 5)->default(0)->comment('대리점 수수료');
            $table->unsignedInteger('sales1_settle_id')->nullable()->comment('대리점 정산 ID');
            //
            $table->integer('sales0_settle_amount')->default(0)->comment('지사 정산금');
            $table->unsignedMediumInteger('sales0_id')->nullable()->comment('하위 대리점');
            $table->float('sales0_fee', 6, 5)->default(0)->comment('하위 대리점 거래 수수료');
            $table->unsignedInteger('sales0_settle_id')->nullable()->comment('하위 대리점 정산 ID');
            //
            $table->integer('pg_id')->default(0)->comment('PG사 id');
            $table->integer('ps_id')->default(0)->comment('PG사 구간 id');
            $table->float('ps_fee', 6, 5)->default(0)->comment('PG사 구간 수수료');
            $table->boolean('pg_settle_type')->default(0)->comment('PG사 정산타입(0=주말포함, 1=주말제외)');
            $table->integer('custom_id')->nullable()->default(0)->comment('커스텀 필터 ID');
            $table->integer('terminal_id')->nullable()->default(0)->comment('장비 타입 ID');
            //            
            $table->integer('mcht_settle_amount')->default(0)->comment('가맹점 정산금');
            $table->float('mcht_fee', 6, 5)->comment('가맹점 수수료');
            $table->float('hold_fee', 6, 5)->comment('보유금액 수수료');      
            $table->tinyInteger('mcht_settle_type')->comment('가맹점 정산타입(D+1, D+2 ..)');
            $table->smallInteger('mcht_settle_fee')->default(0)->comment('가맹점 입금 수수료');
            $table->unsignedInteger('mcht_settle_id')->nullable()->comment('가맹점 정산 ID')->constrained('settle_histories_merchandises')->onDelete('SET NULL');
            //
            $table->date('trx_dt')->comment('거래 날짜');
            $table->time('trx_tm')->comment('거래 시간');
            $table->date('cxl_dt')->nullable()->comment('취소 날짜');
            $table->time('cxl_tm')->nullable()->comment('취소 시간');
            $table->boolean('is_cancel')->default(false)->comment('취소 여부');
            $table->tinyInteger('cxl_seq', false, true)->default(0)->comment('취소 회차');
            $table->integer('amount')->comment('거래 금액');
            $table->tinyInteger('module_type')->comment('모듈 타입(0=장비, 1=수기, 2=인증, 3=간편)');
            $table->string('ord_num', 100)->default('')->comment('주문번호');
            $table->string('mid', 100)->index()->default('')->comment('MID');
            $table->string('tid', 100)->index()->default('')->comment('장비 ID');
            $table->string('trx_id', 100)->default('')->comment('거래번호');
            $table->string('ori_trx_id', 100)->nullable()->comment('원거래번호');
            $table->string('card_num', 20)->comment('거래 카드번호');
            $table->string('issuer', 20)->nullable()->comment('발급사');
            $table->string('acquirer', 20)->nullable()->comment('매입사');
            $table->string('appr_num', 9)->comment('승인번호');
            $table->tinyInteger('installment')->default(0)->comment('할부');
            $table->string('buyer_name', 50)->nullable()->comment('구매자명');
            $table->string('buyer_phone', 20)->nullable()->comment('구매자 휴대폰번호');
            $table->string('item_name', 100)->nullable()->comment('상품명');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();

            $table->unique(['mcht_id', 'cxl_seq', 'appr_num', 'trx_id'], 'duplicate_unique_key');
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
