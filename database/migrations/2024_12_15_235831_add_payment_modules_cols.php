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
            $table->boolean('is_able_bill_key')->default(false)->comment('빌키 사용여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('is_able_bill_key');
        });
    }
};
