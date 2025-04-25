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
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->tinyInteger('deposit_type')->default(0)->comment('입금타입');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_account_histories', function (Blueprint $table) {
            $table->dropColumn('deposit_type');
        });
    }
};
