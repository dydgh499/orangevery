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
        Schema::create('cms_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('oper_id')->nullable()->comment('운영자 ID');
            $table->integer('amount')->nullable()->comment('거래금액');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('계좌명');
            $table->string('acct_bank_name', 30)->nullable()->comment('은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->timestamp('withdraw_book_time')->nullable()->comment('예약시간');
            $table->boolean('withdraw_status')->default(false)->comment('이체예약(0=대기, 1=완료, 2=실패)');
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
