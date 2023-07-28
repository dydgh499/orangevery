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
        Schema::table('payment_sections', function (Blueprint $table) {
            $table->float('trx_fee', 6, 5)->comment('거래 수수료')->change();
        });
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->float('dev_fee', 6, 5)->default(0)->comment('개발사 수수료')->change();
        });
        Schema::table('sf_fee_apply_histories', function (Blueprint $table) {
            $table->float('trx_fee', 6, 5)->nullable()->comment('적용 거래 수수료')->change();
        });

        Schema::table('sf_fee_change_histories', function (Blueprint $table) {
            $table->float('bf_trx_fee', 6, 5)->nullable()->comment('이전 거래 수수료')->change();
            $table->float('aft_trx_fee', 6, 5)->nullable()->comment('이후 거래 수수료')->change();
        });
        Schema::table('mcht_fee_change_histories', function (Blueprint $table) {
            $table->float('bf_trx_fee', 6, 5)->nullable()->comment('이전 거래 수수료')->change();
            $table->float('bf_hold_fee', 6, 5)->nullable()->comment('이전 보유금액 수수료')->change();
            $table->float('aft_trx_fee', 6, 5)->nullable()->comment('이후 거래 수수료')->change();
            $table->float('aft_hold_fee', 6, 5)->nullable()->comment('이후 보유금액 수수료')->change();
        });
        Schema::table('merchandises', function (Blueprint $table) {
            $table->float('sales5_fee', 6, 5)->default(0)->comment('지사 수수료')->change();
            $table->float('sales4_fee', 6, 5)->default(0)->comment('하위지사 수수료')->change();
            $table->float('sales3_fee', 6, 5)->default(0)->comment('총판 수수료')->change();
            $table->float('sales2_fee', 6, 5)->default(0)->comment('하위총판 수수료')->change();
            $table->float('sales1_fee', 6, 5)->default(0)->comment('대리점 수수료')->change();
            $table->float('sales0_fee', 6, 5)->default(0)->comment('하위대리점 수수료')->change();
            $table->float('hold_fee', 6, 5)->default(0)->comment('보유금액 수수료')->change();
            $table->float('trx_fee', 6, 5)->default(0)->comment('거래 수수료')->change();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->float('dev_fee', 6, 5)->default(0)->comment('개발사 거래 수수료')->change();
            $table->float('ps_fee', 6, 5)->default(0)->comment('PG사 구간 수수료')->change();
            $table->float('sales5_fee', 6, 5)->default(0)->comment('지사 수수료')->change();
            $table->float('sales4_fee', 6, 5)->default(0)->comment('하위지사 수수료')->change();
            $table->float('sales3_fee', 6, 5)->default(0)->comment('총판 수수료')->change();
            $table->float('sales2_fee', 6, 5)->default(0)->comment('하위총판 수수료')->change();
            $table->float('sales1_fee', 6, 5)->default(0)->comment('대리점 수수료')->change();
            $table->float('sales0_fee', 6, 5)->default(0)->comment('하위대리점 수수료')->change();
            $table->float('hold_fee', 6, 5)->default(0)->comment('보유금액 수수료')->change();
            $table->float('mcht_fee', 6, 5)->default(0)->comment('가맹점 수수료')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_sections', function (Blueprint $table) {
            $table->float('trx_fee', 6, 4)->comment('거래 수수료')->change();
        });
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->float('dev_fee', 6, 4)->default(0)->comment('개발사 수수료')->change();
        });
        Schema::table('sf_fee_apply_histories', function (Blueprint $table) {
            $table->float('trx_fee', 8, 5)->nullable()->comment('적용 거래 수수료')->change();
        });

        Schema::table('sf_fee_change_histories', function (Blueprint $table) {
            $table->float('bf_trx_fee',8,5)->nullable()->comment('이전 거래 수수료')->change();
            $table->float('aft_trx_fee',8,5)->nullable()->comment('이후 거래 수수료')->change();
        });
        Schema::table('mcht_fee_change_histories', function (Blueprint $table) {
            $table->float('bf_trx_fee',8,5)->nullable()->comment('이전 거래 수수료')->change();
            $table->float('bf_hold_fee',8,5)->nullable()->comment('이전 보유금액 수수료')->change();
            $table->float('aft_trx_fee',8,5)->nullable()->comment('이후 거래 수수료')->change();
            $table->float('aft_hold_fee',8,5)->nullable()->comment('이후 보유금액 수수료')->change();
        });

        Schema::table('merchandises', function (Blueprint $table) {
            $table->float('dev_fee', 6, 4)->default(0)->comment('개발사 거래 수수료')->change();
            $table->float('ps_fee', 6, 4)->default(0)->comment('PG사 구간 수수료')->change();
            $table->float('sales5_fee', 6, 4)->default(0)->comment('지사 수수료')->change();
            $table->float('sales4_fee', 6, 4)->default(0)->comment('하위지사 수수료')->change();
            $table->float('sales3_fee', 6, 4)->default(0)->comment('총판 수수료')->change();
            $table->float('sales2_fee', 6, 4)->default(0)->comment('하위총판 수수료')->change();
            $table->float('sales1_fee', 6, 4)->default(0)->comment('대리점 수수료')->change();
            $table->float('sales0_fee', 6, 4)->default(0)->comment('하위대리점 수수료')->change();
            $table->float('hold_fee', 6, 4)->default(0)->comment('보유금액 수수료')->change();
            $table->float('trx_fee', 6, 4)->default(0)->comment('거래 수수료')->change();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->float('sales5_fee', 6, 4)->default(0)->comment('지사 수수료')->change();
            $table->float('sales4_fee', 6, 4)->default(0)->comment('하위지사 수수료')->change();
            $table->float('sales3_fee', 6, 4)->default(0)->comment('총판 수수료')->change();
            $table->float('sales2_fee', 6, 4)->default(0)->comment('하위총판 수수료')->change();
            $table->float('sales1_fee', 6, 4)->default(0)->comment('대리점 수수료')->change();
            $table->float('sales0_fee', 6, 4)->default(0)->comment('하위대리점 수수료')->change();
            $table->float('hold_fee', 6, 4)->default(0)->comment('보유금액 수수료')->change();
            $table->float('mcht_fee', 6, 4)->default(0)->comment('가맹점 수수료')->change();
        });
    }
};
