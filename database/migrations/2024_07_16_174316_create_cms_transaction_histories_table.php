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
        Schema::create('cms_transaction_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('ct_id')->nullable()->comment('이체 ID');
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
            $table->string('message', 100)->nullable()->default('')->comment('출금결과메세지');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->integer('amount')->nullable()->comment('거래금액');
            $table->string('trans_seq_num', 50)->nullable()->comment('거래번호');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_transaction_histories');
    }
};
