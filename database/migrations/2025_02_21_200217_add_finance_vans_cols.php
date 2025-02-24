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
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->boolean('use_kakao_auth')->default(0)->comment('카카오 인증');
            $table->boolean('use_account_auth')->default(0)->comment('1원 인증');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->dropColumn('use_kakao_auth');
            $table->dropColumn('use_account_auth');
        });
    }
};
