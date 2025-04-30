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
    {   // payment_modules -> pay_window_secure_level 에 통합
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('use_pay_verification_mobile');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->tinyInteger('pay_window_secure_level')->comment('결제창 보안등급(0=결제창 숨김, 1=결제창 노출, 2=PIN 인증, 3=SMS 인증, 4=SCA 인증)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('use_pay_verification_mobile')->default(false)->comment('결제전 휴대폰 인증 사용여부');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->tinyInteger('pay_window_secure_level')->comment('결제창 보안등급(0=결제창 숨김, 1=결제창 노출, 2=PIN 인증, 3=SCA 인증)')->change();
        });
    }
};
