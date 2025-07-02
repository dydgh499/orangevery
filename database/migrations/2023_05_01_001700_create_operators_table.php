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
        Schema::create('operators', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->string('user_name', 30)->index()->comment('ID');
            $table->string('user_pw', 100)->comment('PW');
            $table->string('nick_name', 30)->nullable()->comment('유저 닉네임');
            $table->string('phone_num',20)->nullable()->comment('휴대폰 번호');
            $table->tinyInteger('level')->default(0)->comment('유저 레벨');
            $table->string('profile_img')->nullable()->comment('프로필 이미지');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->boolean('is_lock')->default(0)->comment('계정잠금 여부');
            $table->timestamp('locked_at')->nullable()->comment('계정잠금 시간');
            $table->timestamp('password_change_at')->nullable()->comment('마지막 패스워드 변경시간');
            $table->timestamps();
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->string('google_2fa_secret_key', 255)->nullable()->comment('2FA secret Key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operators');
    }
};
