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
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->date('contract_s_dt')->nullable()->comment('계약 시작일');
            $table->date('contract_e_dt')->nullable()->comment('계약 종료일');
            $table->boolean('is_use_realtime_deposit')->default(false)->comment('실시간 이체 사용여부');
        });
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->tinyInteger('finance_company_num')->nullable()->comment('금융 VAN사 ID');
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->float('dev_fee', 6, 5)->default(0)->comment('개발사 수수료');
            $table->tinyInteger('dev_settle_type')->default(0)->comment('수수료 계산 타입(0=본사 이익 대비 %, 1=거래금액 대비 %)');
            $table->integer('extra_deposit_amount')->default(0)->comment('부가 입금 금액');
            $table->integer('curr_deposit_amount')->default(0)->comment('현재 입금 금액(월단위)');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->float('dev_realtime_fee', 6, 5)->default(0)->comment('개발사 실시간 수수료');
            $table->integer('dev_realtime_settle_amount')->default(0)->comment('개발사 실시간 정산금');
        });
        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('is_use_noti')->default(false)->comment('노티 사용여부');
        });
        Schema::table('realtime_send_histories', function (Blueprint $table) {
            $table->foreignId('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('request_type')->nullable()->comment('요청 타입');
            $table->integer('finance_id')->nullable()->comment('금융 VAN ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('contract_s_dt');
            $table->dropColumn('contract_e_dt');
            $table->dropColumn('is_use_realtime_deposit');
        });
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->dropColumn('finance_company_num');
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('dev_fee');
            $table->dropColumn('dev_settle_type');
            $table->dropColumn('extra_deposit_amount');
            $table->dropColumn('curr_deposit_amount');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('dev_realtime_fee');
            $table->dropColumn('dev_realtime_settle_amount');
        });
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('is_use_noti');
        });
        Schema::table('realtime_send_histories', function (Blueprint $table) {
            $table->dropColumn('mcht_id');
            $table->dropColumn('request_type');
            $table->dropColumn('finance_id');
            //
        });
    }
};
