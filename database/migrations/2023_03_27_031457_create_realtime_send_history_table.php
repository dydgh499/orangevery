<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('realtime_send_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 ID')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('trans_id')->nullable()->comment('거래 ID')->constrained('transactions')->onDelete('SET NULL');
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->string('result_code', 5)->comment('응답 CODE');
            $table->integer('request_type')->nullable()->comment('요청 타입');
            $table->integer('finance_id')->nullable()->comment('금융 VAN ID');
            $table->string('message')->comment('내용');
            $table->integer('amount')->comment('거래 금액');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_bank_name', 30)->nullable()->comment('입금은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->string('trans_seq_num', 20)->nullable()->comment('요청 ID');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('realtime_send_histories');
    }
};
