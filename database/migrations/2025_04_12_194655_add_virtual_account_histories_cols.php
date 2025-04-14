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
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->dropForeign(['trans_id']); // 외래 키 삭제
            $table->dropColumn('result_code');
            $table->dropColumn('note');
            $table->dropColumn('fin_trans_num');
            $table->dropColumn('acct_num');
            $table->dropColumn('acct_name');
            $table->dropColumn('acct_bank_name');
            $table->dropColumn('acct_bank_code');
        });
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->renameColumn('trans_id', 'settle_id');
        });
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK');
            $table->unsignedInteger('settle_id')->comment('정산 ID')->change();
            $table->tinyInteger('cxl_seq')->nullable()->comment('취소 회차');
            $table->tinyInteger('level')->default(0)->comment('유저 LEVEL');
            $table->tinyInteger('deposit_status')->default(0)->comment('입금상태(0=입금대기, 1=입금완료)');
            $table->timestamp('deposit_schedule_time')->nullable()->comment('입금예정시간');
            $table->tinyInteger('withdraw_status')->default(0)->comment('출금상태(0=출금실패, 1=출금완료)');
            $table->timestamp('withdraw_schedule_time')->nullable()->comment('출금예정시간');
            $table->tinyInteger('withdraw_type')->default(0)->comment('출금타입(0=모아서 출금, 1=자동이체)');            
            $table->unsignedSmallInteger('withdraw_fee')->default(0)->comment('출금 수수료');
            $table->string('trx_id', 50)->nullable()->comment('거래번호(즉시출금 전용)');            
        });

        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->unique(['brand_id', 'trx_id', 'trans_type', 'level', 'cxl_seq'], 'duplicate_trx_id_unique_key');
        });

        Schema::table('payment_modules', function (Blueprint $table) {
            $table->unsignedInteger('va_id')->nullable()->comment('정산지갑 ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->renameColumn('settle_id', 'trans_id');
        });
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->dropColumn('withdraw_type');
            $table->dropColumn('trans_result');
            $table->dropColumn('trans_apply_schedule_time');

            $table->dropColumn('deposit_status');
            $table->dropColumn('deposit_schedule_time');
            $table->dropColumn('level');
            $table->dropColumn('brand_id');
            $table->dropColumn('trx_id');
            $table->dropColumn('withdraw_fee');

            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->string('note', 100)->nullable()->default('')->comment('메모사항');
            $table->string('fin_trans_num', 50)->index()->nullable()->comment('거래번호');
            $table->string('acct_num')->nullable()->comment('계좌번호');
            $table->string('acct_name')->nullable()->comment('계좌명');
            $table->string('acct_bank_name')->nullable()->comment('은행명');
            $table->string('acct_bank_code')->nullable()->comment('은행코드');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('va_id');
        });
    }
};
