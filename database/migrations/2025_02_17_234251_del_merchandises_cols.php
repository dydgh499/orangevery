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
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('use_saleslip_sell');
            $table->dropColumn('is_hide_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('use_saleslip_sell')->default(false)->comment('매출전포 가맹점 표기정보');
            $table->boolean('is_hide_account')->default(false)->comment('계좌 숨김여부');
        });
    }
};
