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
        Schema::table('transactions', function (Blueprint $table) {
            $table->tinyInteger('trx_status')->default()->comment('거래상태 (입금대기=0, 1=결제성공, 3=결제실패, 5=정산성공, 7=정산실패)');
            $table->integer('ctb_id')->nullable()->comment('이체예약 ID');
            
            $table->dropColumn('buyer_name');
            $table->dropColumn('buyer_phone');
            $table->dropColumn('item_name');
            $table->dropColumn('issuer_code');
            $table->dropColumn('acquirer_code');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('trx_status');
            $table->dropColumn('ctb_id');
            
            $table->string('buyer_name', 50)->nullable()->comment('구매자명');
            $table->string('buyer_phone', 20)->nullable()->comment('구매자 휴대폰번호');
            $table->string('item_name', 100)->nullable()->comment('상품명');
            $table->string('issuer_code', 3)->nullable()->comment('발급사 코드');
            $table->string('acquirer_code', 3)->nullable()->comment('매입사 코드');
        });
    }
};
