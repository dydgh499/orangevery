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
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->unsignedInteger('send_type')->default(0)->comment('발송타입(0=전체, 1=승인건만 발송, 2=취소건만 발송)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->dropColumn('send_type');
        });
    }
};
