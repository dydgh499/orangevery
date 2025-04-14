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
        Schema::table('operators', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->boolean('is_notice_realtime_warning')->default(false)->comment('실시간 경고사항 알림');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('is_notice_realtime_warning');
        });
    }
};
