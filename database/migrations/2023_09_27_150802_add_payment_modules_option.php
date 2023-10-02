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
            $table->integer('pay_single_limit')->default(0)->comment('결제 단건 한도(단위:만원)');
            $table->integer('pay_dupe_least')->default(0)->comment('중복거래 하한금');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('pay_single_limit');
            $table->dropColumn('pay_dupe_least');
        });
    }
};
