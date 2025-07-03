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
        Schema::create('bill_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pmod_id')->nullable()->comment('결제모듈 ID')->constrained('payment_modules')->onDelete('SET NULL');
            $table->integer('oper_id')->nullable()->comment('운영자 ID');
            $table->string('buyer_name')->nullable()->comment('구매자명');
            $table->string('buyer_phone')->nullable()->comment('구매자 휴대폰번호');
            $table->string('issuer')->default('')->comment('발급카드사');
            $table->string('bill_key')->default('')->comment('BILL KEY');
            $table->string('ori_bill_key')->default('')->comment('결제 KEY');
            $table->string('nick_name', 50)->nullable()->comment('별칭');
            $table->string('card_num', 255)->default('')->comment('카드번호');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_keys');
    }
};
