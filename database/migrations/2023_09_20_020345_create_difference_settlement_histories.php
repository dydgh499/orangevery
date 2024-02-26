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
        Schema::create('difference_settlement_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trans_id')->nullable()->comment('거래 ID')->constrained('transactions')->onDelete('SET NULL');
            $table->string('settle_result_code', 4)->index()->nullable()->comment('정산 결과코드');
            $table->string('card_company_result_code', 3)->nullable()->comment('카드사 결과코드');
            $table->string('settle_result_msg', 50)->nullable()->comment('카드사 결과 메세지');
            $table->string('card_company_result_msg', 150)->nullable()->comment('카드사 결과 메세지');
            $table->string('mcht_section_code')->comment('가맹점 구분');
            $table->string('mcht_section_name')->nullable()->comment('가맹점 구분명');            
            $table->date('req_dt')->comment('정산요청 날짜');
            $table->date('settle_dt')->comment('정산 날짜');
            $table->integer('supply_amount')->comment('공급가액');
            $table->integer('vat_amount')->comment('부가세');
            $table->integer('settle_amount')->comment('차액 정산금');            
            $table->string('settle_result_msg', 50)->nullable()->comment('카드사 결과 메세지');
            $table->string('card_company_result_msg', 150)->nullable()->comment('카드사 결과 메세지');
            $table->string('mcht_section_name')->nullable()->comment('가맹점 구분명');            
            $table->timestamps();
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
