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
        Schema::table('payment_modules', function (Blueprint $table) {
            /*  2024-05-16 (버디페이 요청사항)
                1. 특정 시간대에만 결제한도에 따라 옵션 적용
                2. 옵션(지정시간 결제한도 초과 시): 결제 금지, 즉시출금 금지
            */
            $table->time('specified_limit_s_tm')->nullable()->comment('지정시간 제한 시작시간');
            $table->time('specified_limit_e_tm')->nullable()->comment('지정시간 제한 종료시간');
            $table->smallInteger('specified_limit_amount', false, true)->default(0)->comment('지정시간 제한 상한금');
            $table->tinyInteger('specified_limit_option')->default(0)->comment('0=결제 금지, 1=즉시출금 금지');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            //
        });
    }
};
