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
        Schema::table('settle_histories_merchandises', function (Blueprint $table) {
            $table->tinyInteger('deposit_status')->change();
        });
        Schema::table('settle_histories_salesforces', function (Blueprint $table) {
            $table->tinyInteger('deposit_status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settle_histories_merchandises', function (Blueprint $table) {
            $table->boolean('deposit_status')->change();
        });
        Schema::table('settle_histories_salesforces', function (Blueprint $table) {
            $table->boolean('deposit_status')->change();
        });
    }
};
