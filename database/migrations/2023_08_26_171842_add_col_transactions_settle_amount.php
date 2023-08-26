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
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('brand_settle_amount')->default(0)->comment('본사 정산금');
            $table->integer('dev_settle_amount')->default(0)->comment('개발사 정산금');
            $table->integer('sales0_settle_amount')->default(0)->comment('지사 정산금');
            $table->integer('sales1_settle_amount')->default(0)->comment('하위 지사 정산금');
            $table->integer('sales2_settle_amount')->default(0)->comment('총판 정산금');
            $table->integer('sales3_settle_amount')->default(0)->comment('하위총판 정산금');
            $table->integer('sales4_settle_amount')->default(0)->comment('대리점 정산금');
            $table->integer('sales5_settle_amount')->default(0)->comment('하위대리점 정산금');
            $table->integer('mcht_settle_amount')->default(0)->comment('가맹점 정산금');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('brand_settle_amount');
            $table->dropColumn('dev_settle_amount');
            $table->dropColumn('sales0_settle_amount');
            $table->dropColumn('sales1_settle_amount');
            $table->dropColumn('sales2_settle_amount');
            $table->dropColumn('sales3_settle_amount');
            $table->dropColumn('sales4_settle_amount');
            $table->dropColumn('sales5_settle_amount');
            $table->dropColumn('mcht_settle_amount');
        });
    }
};
