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
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->string('user_name', 30)->index()->comment('ID');
            $table->string('user_pw', 100)->comment('PW');
            $table->string('nick_name', 30)->nullable()->comment('유저 닉네임');
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->tinyInteger('level')->default(0)->comment('유저 레벨');
            $table->string('profile_img')->nullable()->comment('프로필 이미지');
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
        Schema::dropIfExists('operators');
    }
};
