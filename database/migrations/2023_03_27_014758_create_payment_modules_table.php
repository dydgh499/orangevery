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
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('pg_id')->nullable()->comment('PG사 id');
            $table->integer('ps_id')->nullable()->comment('PG사 구간 id');
            $table->mediumInteger('settle_fee')->default(0)->comment('건별 수수료');
            $table->tinyInteger('module_type')->default(0)->comment('모듈 타입(0=장비, 1=수기, 2=인증, 3=간편, 4=실시간 이체, 5=비인증 장비)');
            $table->string('api_key', 100)->default('')->comment('API KEY');
            $table->string('sub_key', 100)->default('')->comment('SUB KEY');
            $table->string('p_mid', 50)->default('')->comment('상위 mid');
            $table->string('mid', 50)->default('')->comment('mid');
            $table->string('tid', 50)->default('')->comment('tid');
            $table->string('serial_num', 50)->default('')->comment('serial num');
            //-------------------------- comm
            $table->integer('terminal_id')->nullable()->comment('장비 종류');
            //-------------------------- 4
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
            $table->tinyInteger('fin_trx_delay')->nullable()->comment('실시간 이체 딜레이(15,30,45,60)');
            //-------------------------- option
            $table->tinyInteger('cxl_type')->default(2)->comment('취소타입(0=취소금지, 1=이체시간 -5분, 2=당일허용)');
            $table->boolean('is_old_auth')->default(false)->comment('수기결제 타입(0=비인증, 1=구인증)');
            $table->tinyInteger('pay_window_secure_level')->comment('결제창 보안등급(0=결제창 숨김, 1=결제창 노출, 2=PIN 인증, 3=SMS 인증, 4=SCA 인증)');
            
            $table->tinyInteger('installment')->default(0)->comment('할부 한도(0~12)');
            $table->tinyInteger('pay_dupe_limit')->default(0)->comment('동일카드 결제허용 회수');
            $table->integer('abnormal_trans_limit')->default(0)->comment('이상거래 한도(단위:만원)');
            $table->integer('pay_year_limit')->default(0)->comment('결제 연 한도(단위:만원)');
            $table->integer('pay_month_limit')->default(0)->comment('결제 월 한도(단위:만원)');
            $table->integer('pay_day_limit')->default(0)->comment('결제 일 한도(단위:만원)');
            $table->time('pay_disable_s_tm')->nullable()->comment('결제 금지 시작 시간');
            $table->time('pay_disable_e_tm')->nullable()->comment('결제 금지 종료 시간');
            $table->integer('pay_single_limit')->default(0)->comment('결제 단건 한도(단위:만원)');
            $table->integer('pay_dupe_least')->default(0)->comment('중복거래 하한금');
            $table->boolean('use_realtime_deposit')->default(false)->comment('실시간 이체 사용여부');

            $table->string('pay_key', 100)->default('')->comment('결제 KEY');
            $table->string('filter_issuers', 200)->default('[]')->comment('카드사 필터');
            $table->string('note', 200)->nullable()->default('')->comment('별칭');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
            $table->tinyInteger('payment_term_min', false, true)->default(0)->comment('결제 텀 체크');
            $table->string('sign_key', 100)->default('')->comment('서명 KEY');
            $table->tinyInteger('pay_window_extend_hour')->default(1)->comment('결제창 연장시간');$table->unsignedInteger('pay_limit_type')->default(0)->comment('결제제한타입(0=제한없음, 1=주말금지, 2=공휴일금지, 3=주말+공휴일 금지)');
            $table->unsignedInteger('withdraw_limit_type')->default(0)->comment('출금제한타입(0=제한없음, 1=주말금지, 2=공휴일금지, 3=주말+공휴일 금지)');
            $table->unsignedInteger('withdraw_business_limit')->default(0)->comment('일 출금한도(영업일)');
            $table->unsignedInteger('withdraw_holiday_limit')->default(0)->comment('일 출금한도(휴무일)');
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
