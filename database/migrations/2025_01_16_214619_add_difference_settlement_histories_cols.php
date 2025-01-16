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
        Schema::table('difference_settlement_histories', function (Blueprint $table) {
            $table->dropColumn('card_company_result_code');
            $table->dropColumn('card_company_result_msg');
            $table->dropColumn('mcht_section_name');

            $table->string('mcht_section_code', 1)->nullable()->change();
            $table->date('settle_dt')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('difference_settlement_histories', function (Blueprint $table) {
            $table->string('card_company_result_code', 3)->nullable()->comment('카드사 결과코드');
            $table->string('card_company_result_msg', 150)->nullable()->comment('카드사 결과 메세지');
            $table->string('mcht_section_name', 20)->nullable()->comment('가맹점 구분명');  
        });
    }
};
