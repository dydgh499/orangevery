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
            $table->foreignId('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('request_type')->nullable()->comment('요청 타입');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realtime_send_histories', function (Blueprint $table) {
            $table->dropColumn('mcht_id');
            $table->dropColumn('request_type');
            //
        });
    }
};
