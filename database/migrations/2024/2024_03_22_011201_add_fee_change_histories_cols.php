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
        Schema::table('mcht_fee_change_histories', function (Blueprint $table) {
            $table->date('apply_dt')->nullable()->comment('적용일');
        });
        Schema::table('sf_fee_change_histories', function (Blueprint $table) {
            $table->date('apply_dt')->nullable()->comment('적용일');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
