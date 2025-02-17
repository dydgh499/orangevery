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
        Schema::table('salesforces', function (Blueprint $table) {
            $table->unsignedInteger('mcht_pg_id')->default(null)->comment('원천사 고유번호');
            $table->unsignedInteger('mcht_ps_id')->default(null)->comment('구간 고유번호');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
            $table->dropColumn('mcht_pg_id');
            $table->dropColumn('mcht_ps_id');
        });
    }
};
