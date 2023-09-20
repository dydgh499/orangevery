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
            $table->string('settle_result_msg', 50)->nullable()->comment('카드사 결과 메세지');
            $table->string('card_company_result_msg', 150)->nullable()->comment('카드사 결과 메세지');
            $table->string('mcht_section_name')->nullable()->comment('가맹점 구분명');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('difference_settlement_histories');
    }
};
