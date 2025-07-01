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
        Schema::create('cms_transaction_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->boolean('is_withdraw')->default(false)->comment('출금 여부(1=출금, 0=입금)');
            $table->integer('amount')->nullable()->comment('거래금액');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('계좌명');
            $table->string('acct_bank_name', 30)->nullable()->comment('은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->string('note', 255)->nullable()->default('')->comment('메모사항');
            $table->timestamp('withdraw_book_time')->index()->nullable()->comment('예약시간');
            $table->boolean('withdraw_status')->default(false)->comment('이체예약(0=이체대기, 1=이체완료)');
            $table->string('trans_seq_num', 50)->nullable()->comment('거래번호(출금 전용)');  
            $table->string('message', 100)->nullable()->default('')->comment('출금결과메세지');
            $table->timestamps();
            $table->unique(['brand_id', 'trans_seq_num', 'is_withdraw'], 'duplicate_trx_id_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_transactions_column_apply_books');
    }
};
