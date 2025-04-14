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
        Schema::create('gmids', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 ID');
            $table->string('user_name', 50)->default('')->comment('ID');
            $table->string('user_pw', 100)->comment('PW');
            $table->string('g_mid', 50)->default('')->comment('GMID');
            $table->string('phone_num', 100)->nullable()->comment('휴대폰 번호');
            $table->string('nick_name', 100)->nullable()->comment('대표자명'); //enc
            $table->string('profile_img')->nullable()->comment('프로필 이미지');
            $table->timestamp('password_change_at')->nullable()->comment('마지막 패스워드 변경일');
            $table->boolean('is_lock')->default(0)->comment('계정잠금 여부');
            $table->timestamp('locked_at')->nullable()->comment('계정잠금 시간');
            $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인시간');
            $table->string('last_login_ip', 100)->nullable()->comment('마지막 로그인 IP');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmids');
    }
};
