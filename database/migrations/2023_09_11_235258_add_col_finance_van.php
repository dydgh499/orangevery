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
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->tinyInteger('finance_company_num')->nullable()->comment('금융 VAN사 ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->dropColumn('finance_company_num');
        });
    }
};
