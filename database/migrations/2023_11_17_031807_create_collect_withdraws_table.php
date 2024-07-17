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
        Schema::create('collect_withdraws', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('사용 가맹점 ID')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('mcht_settle_id')->nullable()->comment('정산 ID');
            $table->string('trans_seq_num', 50)->nullable()->index()->comment('요청 ID');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->string('message', 100)->nullable()->comment('내용');
            $table->integer('withdraw_amount')->default(0)->comment('출금 금액');
            $table->integer('withdraw_fee')->default(0)->comment('출금 수수료');
            $table->date('withdraw_date')->nullable()->comment('출금 날짜');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('예금주명');
            $table->string('acct_bank_name', 30)->nullable()->comment('입금은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collect_withdraws');
    }
};
