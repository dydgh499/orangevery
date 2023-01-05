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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->comment('브랜드 FK');
            $table->integer('group_id')->default(0)->comment('GID');
            $table->string('nick_nm')->default('')->comment('유저 닉네임');
            $table->string('phone', 30)->comment('ID(휴대폰 번호)');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->comment('회원 이미지');
            $table->integer('level')->default(0)->comment('유저 레벨');
            $table->float('fees_rate')->default(0)->comment('수수료율');
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
        Schema::dropIfExists('users');
    }
};
