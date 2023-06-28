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
        Schema::create('finance_vans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('fin_type')->nullable()->comment('실시간 타입');
            $table->tinyInteger('balance_status')->default(5)->comment('잔고 상태(0=잔고없음, 5=충분함)');
            $table->float('dev_fee', 6, 4)->default(0)->comment('개발사 수수료');
            $table->string('api_key', 50)->nullable()->comment('API_KEY');
            $table->string('sms_key', 50)->nullable()->comment('문자 API');
            $table->string('sms_id', 50)->nullable()->comment('문자 API ID');
            $table->string('sms_sender', 20)->nullable()->comment('문자 발신자');
            $table->string('sms_receive_phone', 20)->nullable()->comment('문자 수신자 번호');
            $table->integer('min_balance_limit')->default(0)->comment('최소 알림금액(단위:만원)');
            $table->string('corp_code', 15)->nullable()->comment('법인 코드');
            $table->string('corp_name', 20)->nullable()->comment('법인 명');
            $table->string('bank_code', 3)->nullable()->comment('은행 코드');
            $table->string('nick_name', 20)->nullable()->comment('별칭');
            $table->string('withdraw_acct_num', 20)->nullable()->comment('출금 통장 번호');
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
        Schema::dropIfExists('finance_vans');
    }
};
