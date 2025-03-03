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
        Schema::create('virtual_account_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('va_id')->nullable()->comment('가상계좌 ID');
            $table->unsignedInteger('trans_id')->nullable()->comment('거래 ID');
            $table->integer('trans_amount')->nullable()->comment('거래금액');
            $table->boolean('trans_type')->default(false)->comment('입금/출금');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->string('note', 100)->nullable()->default('')->comment('메모사항');
            $table->string('fin_trans_num', 50)->index()->nullable()->comment('거래번호');

            $table->string('acct_num')->nullable()->comment('가상계좌번호');
            $table->string('acct_name')->nullable()->comment('가상계좌명');
            $table->string('acct_bank_name')->nullable()->comment('은행명');
            $table->string('acct_bank_code')->nullable()->comment('은행코드');
            $table->timestamps();
        });

        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->foreign('va_id')->references('id')->on('virtual_accounts')->onDelete('SET NULL');
            $table->foreign('trans_id')->references('id')->on('transactions')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_account_histories');
    }
};
