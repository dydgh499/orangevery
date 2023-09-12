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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
