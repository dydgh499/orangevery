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
            $table->date('contract_s_dt')->nullable()->comment('계약 시작일');
            $table->date('contract_e_dt')->nullable()->comment('계약 종료일');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('contract_s_dt');
            $table->dropColumn('contract_e_dt');
        });
    }
};
