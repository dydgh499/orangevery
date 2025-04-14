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
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('p_mid', 10)->default('')->comment('PMID');
            $table->string('mid', 10)->default('')->comment('mid');
            $table->string('api_key', 100)->default('')->comment('api_key');
            $table->string('sub_key', 100)->default('')->comment('sub_key');
        });
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->tinyInteger('payment_term_min', false, true)->default(0)->comment('결제 텀 체크');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            //
        });
    }
};
