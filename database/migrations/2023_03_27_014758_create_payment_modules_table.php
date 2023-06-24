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
            $table->integer('pg_id')->default(0)->comment('PG사 id');
            $table->integer('ps_id')->default(0)->comment('PG사 구간 id');
            $table->integer('settle_type')->default(0)->comment('정산일(D+1, D+2 ..)');
            $table->smallInteger('settle_fee')->default(0)->comment('입금 수수료');
            $table->tinyInteger('module_type')->default(0)->comment('모듈 타입(0=단말기, 1=수기, 2=인증, 3=간편, 4=실시간 이체, 5=비인증 단말기)');
            $table->string('api_key', 100)->default('')->comment('API KEY');
            $table->string('sub_key', 100)->default('')->comment('SUB KEY');
            $table->string('mid', 50)->default('')->comment('mid');
            $table->string('tid', 50)->default('')->comment('tid');
            $table->string('serial_num', 50)->default('')->comment('serial num');
            //-------------------------- comm
            $table->integer('terminal_id')->default(0)->comment('단말기 종류');
            $table->smallInteger('comm_settle_fee')->default(0)->comment('통신비 입금 수수료');
            $table->tinyInteger('comm_settle_type')->default(0)->comment('통신비 정산일');
            $table->tinyInteger('comm_calc_level')->default(0)->comment('정산주체(sales id), -1=본인, 125=개발사, 128=본사');
            $table->integer('under_sales_amt')->default(0)->comment('매출미달 차감금');
            $table->date('begin_dt')->nullable()->comment('개통일');
            $table->date('ship_out_dt')->nullable()->comment('출고일');
            $table->boolean('ship_out_stat')->nullable()->comment('출고 상태');
            //-------------------------- 3
            $table->integer('rt_id')->nullable()->comment('실시간 이체 ID');
            $table->tinyInteger('rt_trx_delay')->nullable()->comment('실시간 이체 딜레이(15,30,45,60)');
            $table->tinyInteger('rt_cxl_type')->nullable()->comment('실시간 취소타입(0=취소금지, 1=이체시간 -5분)');
            //-------------------------- option
            $table->boolean('is_old_auth')->default(false)->comment('수기결제 타입(0=비인증, 1=구인증)');
            $table->boolean('use_saleslip_prov')->default(false)->comment('매출전표 공급자 사용 여부(0=사용, 1=본사)');
            $table->boolean('use_saleslip_sell')->default(false)->comment('매출전표 판매자 사용 여부(0=사용, 1=본사)');
            $table->tinyInteger('installment')->default(0)->comment('할부 한도(0~12)');
            $table->string('note', 200)->default('')->comment('비고');
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
