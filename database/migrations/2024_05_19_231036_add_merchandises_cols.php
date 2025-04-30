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
        //single_payment_limit_s_tm
        Schema::table('merchandises', function (Blueprint $table) {
            $table->time('phone_auth_limit_s_tm')->nullable()->comment('휴대폰 인증 허용 시작시간');
            $table->time('phone_auth_limit_e_tm')->nullable()->comment('휴대폰 인증 허용 종료시간');
            $table->tinyInteger('phone_auth_limit_count', false, true)->default(0)->comment('휴대폰 인증 허용 회수');
            
            $table->time('single_payment_limit_s_tm')->nullable()->comment('단일 결제한도 하향 시작시간');
            $table->time('single_payment_limit_e_tm')->nullable()->comment('단일 결제한도 하향 종료시간');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            //
        });
    }
};
