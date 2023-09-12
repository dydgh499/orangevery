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
        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('use_noti')->default(false)->comment('노티 사용여부');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->boolean('use_realtime_deposit')->default(false)->comment('실시간 이체 사용여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('use_noti');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('use_realtime_deposit');
        });
    }
};
