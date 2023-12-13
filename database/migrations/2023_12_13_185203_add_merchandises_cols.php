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
        Schema::table('merchandises', function (Blueprint $table) {
            $table->mediumInteger('mcht_withdraw_fee')->default(0)->comment('직접출금 수수료(가맹점)');
            $table->mediumInteger('withdraw_fee')->default(0)->comment('출금 수수료');
            $table->tinyInteger('tax_category_type')->default(0)->comment('면세사업자 파라미터(0=면세, 1=과세, 2=복합)');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            //
        });
    }
};
