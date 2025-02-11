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
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->unsignedInteger('withdraw_limit_type')->default(0)->comment('출금제한타입(0=제한없음, 1=주말금지, 2=공휴일금지, 3=주말+공휴일 금지)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('withdraw_limit_type');
        });
    }
};
