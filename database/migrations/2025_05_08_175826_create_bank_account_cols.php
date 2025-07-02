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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->string('acct_bank_name', 30)->nullable()->comment('입금은행명');
            $table->string('acct_num', 20)->nullable()->comment('입금 계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('예금주명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');      
            $table->string('note', 100)->nullable()->default('')->comment('메모사항');
            $table->boolean('is_checked')->default(false)->comment('검증 여부(0=실패, 1=성공)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
