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
        /*
            22. 로그인한 아이피와 요청 IP가 다르면 차단
            23. 장기 미접속 잠금처리
        */
        Schema::table('merchandises', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인시간');
            $table->string('last_login_ip', 100)->nullable()->comment('마지막 로그인 IP');
        });
        Schema::table('salesforces', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인시간');
            $table->string('last_login_ip', 100)->nullable()->comment('마지막 로그인 IP');
        });
        Schema::table('operators', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인시간');
            $table->string('last_login_ip', 100)->nullable()->comment('마지막 로그인 IP');
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
