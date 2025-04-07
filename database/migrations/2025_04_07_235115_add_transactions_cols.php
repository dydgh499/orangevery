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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('issuer_code', 3)->nullable()->comment('발급사 코드');
            $table->string('acquirer_code', 3)->nullable()->comment('매입사 코드');
            $table->tinyInteger('round_type')->default(0)->comment('정산금 계산방식(0=반올림, 1=올림, 2=내림)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('issuer_code');
            $table->dropColumn('acquirer_code');
            $table->dropColumn('round_type');
        });
    }
};
