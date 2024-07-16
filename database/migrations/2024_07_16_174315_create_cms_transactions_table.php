<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *  - 입금받지 않았다면, 아직 입금받지 않았습니다.
     *  - 입금을 받았다면, 오늘 입금받은 금액 + 상위사 수수료 .=. 오늘 정산할 금액
     *     - 차액이 일정 %보다 클시
     * NO. 입금받은 금융 VAN, 입금금액, 입금자명, 입금시간
     * 
     * 거래 VAN ID, 거래타입(입금, 출금), 거래시간, 거래금액, 거래번호, 메모사항(입금자명, 예금주,)
     *  - ()
     */
    public function up(): void
    {
        Schema::create('cms_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->boolean('is_withdraw')->default(false)->comment('출금 여부');
            $table->timestamp('trx_at')->index()->nullable()->comment('거래발생시간(취소, 승인)');
            $table->string('trans_seq_num', 20)->nullable()->comment('거래번호');
            $table->integer('amount')->nullable()->comment('거래금액');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('계좌명');
            $table->string('acct_bank_name', 30)->nullable()->comment('은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->string('note', 255)->nullable()->default('')->comment('메모사항');
            $table->string('message', 100)->nullable()->default('')->comment('출금결과메세지');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_transactions');
    }
};
