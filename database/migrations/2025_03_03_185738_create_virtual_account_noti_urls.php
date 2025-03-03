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
        Schema::create('virtual_account_noti_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('va_id')->nullable()->comment('가상계좌 ID');
            $table->string('send_url')->comment('발송 url');
            $table->string('note', 200)->nullable()->comment('비고');
            $table->boolean('noti_status')->default(true)->comment('사용 여부(0=미사용, 1=사용)');
            $table->timestamps();
        });

        Schema::table('virtual_account_noti_urls', function (Blueprint $table) {
            $table->foreign('va_id')->references('id')->on('virtual_accounts')->onDelete('SET NULL');
        });

        Schema::table('virtual_account_noti_histories', function (Blueprint $table) {
            $table->foreign('va_history_id')->references('id')->on('virtual_account_histories')->onDelete('SET NULL');
            $table->foreign('va_noti_url_id')->references('id')->on('virtual_account_noti_urls')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_account_noti_urls');
    }
};
