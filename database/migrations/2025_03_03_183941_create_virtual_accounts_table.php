<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 ID');
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('가맹점 ID');
            $table->string('balance')->default('0')->comment('현재잔액');
            $table->string('account_code', 30)->nullable()->comment('계좌 코드');
            $table->string('account_name', 150)->nullable()->comment('계좌 별칭');
            $table->integer('fin_id')->nullable()->comment('금융 VAN ID');
            $table->tinyInteger('fin_trx_delay')->nullable()->comment('이체 딜레이(15,30,45,60)');
            $table->tinyInteger('withdraw_type')->default(0)->comment('출금타입(0=모아서 출금, 1=자동이체)');
            $table->unsignedInteger('withdraw_limit_type')->default(0)->comment('출금제한타입(0=제한없음, 1=주말금지, 2=공휴일금지, 3=주말+공휴일 금지)');
            $table->unsignedInteger('withdraw_business_limit')->default(0)->comment('일 출금한도(영업일)');
            $table->unsignedInteger('withdraw_holiday_limit')->default(0)->comment('일 출금한도(휴무일)');
            $table->timestamps();
        });
        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises')->onDelete('SET NULL');
            $table->unique(['account_code'], 'duplicate_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_accounts');
    }
};
