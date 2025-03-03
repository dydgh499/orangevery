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
        Schema::create('virtual_account_noti_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('va_history_id')->nullable()->comment('가상계좌 거래통지 ID');
            $table->unsignedInteger('va_noti_url_id')->nullable()->comment('가상계좌 노티 URL ID');
            $table->integer('http_code')->comment('응답 HTTP CODE');
            $table->string('response')->comment('응답 내용');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_account_noti_histories');
    }
};
