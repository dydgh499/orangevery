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
        Schema::create('payment_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('pg_id')->nullable()->comment('PG사 id');
            $table->integer('ps_id')->nullable()->comment('PG사 구간 id');
            $table->integer('settle_type')->default(0)->comment('정산일(D+1, D+2 ..)');
            $table->mediumInteger('settle_fee')->default(0)->comment('입금 수수료');
            $table->tinyInteger('module_type')->default(0)->comment('모듈 타입(0=장비, 1=수기, 2=인증, 3=간편, 4=실시간 이체, 5=비인증 장비)');
            $table->string('api_key', 100)->default('')->comment('API KEY');
            $table->string('sub_key', 100)->default('')->comment('SUB KEY');
            $table->string('mid', 50)->default('')->comment('mid');
            $table->string('tid', 50)->default('')->comment('tid');
            $table->string('serial_num', 50)->default('')->comment('serial num');
            //-------------------------- comm
            $table->integer('terminal_id')->nullable()->comment('장비 종류');
            $table->tinyInteger('comm_settle_day')->default(0)->comment('통신비 정산일');
            $table->smallInteger('comm_settle_fee')->default(0)->comment('통신비 입금 수수료');
            $table->tinyInteger('comm_settle_day')->default(0)->comment('통신비 정산 타입(개통월 부터=0, 개통월 익월=1, 개통월 익익월=2)');
            $table->tinyInteger('comm_calc_level')->default(0)->comment('정산주체(sales id), -1=본인, 125=개발사, 128=본사');
            $table->integer('under_sales_amt')->default(0)->comment('매출미달 차감금');
            $table->date('begin_dt')->nullable()->comment('개통일');
            $table->date('ship_out_dt')->nullable()->comment('출고일');
            $table->tinyInteger('ship_out_stat')->nullable()->comment('출고 상태(0=공장비, 1=입고, 2=출고, 3=해지)');
            //-------------------------- 4
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
            $table->tinyInteger('fin_trx_delay')->nullable()->comment('실시간 이체 딜레이(15,30,45,60)');
            //-------------------------- option
            $table->tinyInteger('cxl_type')->default(2)->comment('취소타입(0=취소금지, 1=이체시간 -5분, 2=당일허용)');
            $table->boolean('is_old_auth')->default(false)->comment('수기결제 타입(0=비인증, 1=구인증)');
            $table->boolean('show_pay_view')->default(true)->comment('결제창 사용(0=미사용, 1=사용)');
            
            $table->tinyInteger('installment')->default(0)->comment('할부 한도(0~12)');
            $table->tinyInteger('pay_dupe_limit')->default(0)->comment('중복결제 허용회수');
            $table->integer('abnormal_trans_limit')->default(0)->comment('이상거래 한도(단위:만원)');
            $table->integer('pay_year_limit')->default(0)->comment('결제 연 한도(단위:만원)');
            $table->integer('pay_month_limit')->default(0)->comment('결제 월 한도(단위:만원)');
            $table->integer('pay_day_limit')->default(0)->comment('결제 일 한도(단위:만원)');
            $table->time('pay_disable_s_tm')->nullable()->comment('결제 금지 시작 시간');
            $table->time('pay_disable_e_tm')->nullable()->comment('결제 금지 종료 시간');
            $table->integer('pay_single_limit')->default(0)->comment('결제 단건 한도(단위:만원)');
            $table->integer('pay_dupe_least')->default(0)->comment('중복거래 하한금');

            $table->string('pay_key', 100)->default('')->comment('API KEY');
            $table->string('filter_issuers', 200)->default('[]')->comment('카드사 필터');
            $table->string('note', 200)->nullable()->default('')->comment('별칭');
            $table->integer('last_settle_month')->length(3)->default(0)->comment('마지막 정산달');
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
        Schema::dropIfExists('payment_modules');
    }
};
