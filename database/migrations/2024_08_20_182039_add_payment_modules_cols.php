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
            // 컬럼 이름 변경
            $table->renameColumn('show_pay_view', 'pay_window_secure_level');
        });
        // 별도의 Schema::table 호출에서 컬럼 속성 변경 및 추가
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->tinyInteger('pay_window_secure_level')->default(1)->comment('결제창 보안등급(0=결제창 숨김, 1=결제창 노출, 2=PIN 인증, 3=SMS 인증, 4=SCA 인증)')->change();
            $table->tinyInteger('pay_window_extend_hour')->default(1)->comment('결제창 연장시간');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->renameColumn('pay_window_secure_level', 'show_pay_view');
        });

        Schema::table('payment_modules', function (Blueprint $table) {
            // 컬럼 이름을 다시 원래대로 변경
            $table->dropColumn('pay_window_extend_hour'); // 새로 추가된 컬럼을 삭제
            $table->boolean('show_pay_view')->default(true)->comment('결제창 사용(0=미사용, 1=사용)')->change();
        });
    }
};
