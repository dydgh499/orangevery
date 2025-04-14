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
        Schema::create('noti_send_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 ID')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('trans_id')->nullable()->comment('거래 ID')->constrained('transactions')->onDelete('SET NULL');
            $table->integer('http_code')->comment('응답 HTTP CODE');
            $table->string('send_url')->comment('발송 url');
            $table->tinyInteger('retry_count')->comment('재시도 회수');
            $table->string('message')->comment('내용');
            $table->string('temp', 500)->nullable()->comment('임시 값');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
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
        Schema::dropIfExists('noti_send_histories');
    }
};
