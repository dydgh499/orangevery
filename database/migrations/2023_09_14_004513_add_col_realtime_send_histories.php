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
        Schema::table('realtime_send_histories', function (Blueprint $table) {
            $table->integer('finance_id')->nullable()->comment('금융 VAN ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realtime_send_histories', function (Blueprint $table) {
            $table->dropColumn('finance_id');
        });
    }
};
