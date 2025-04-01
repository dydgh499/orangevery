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
        Schema::table('settle_histories_merchandises_deposits', function (Blueprint $table) {
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
        });
        Schema::table('settle_histories_salesforces_deposits', function (Blueprint $table) {
            $table->integer('fin_id')->nullable()->comment('실시간 이체 ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settle_histories_merchandises_deposits', function (Blueprint $table) {
            $table->dropColumn('fin_id');
        });
        Schema::table('settle_histories_salesforces_deposits', function (Blueprint $table) {
            $table->dropColumn('fin_id');
        });
    }
};
