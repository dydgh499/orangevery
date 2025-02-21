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
        Schema::table('sales_recommender_codes', function (Blueprint $table) {
            $table->float('sales_fee', 7, 5)->default(0)->comment('영업점 수수료');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_recommender_codes', function (Blueprint $table) {
            $table->dropColumn('sales_fee');
        });
    }
};
