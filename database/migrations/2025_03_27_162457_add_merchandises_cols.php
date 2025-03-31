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
            $table->string('g_mid', 30)->default('')->index()->comment('GMID');
        });
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('is_show_fee');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('g_mid');
        });
        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('is_show_fee')->default(false)->comment('수수료율 노출여부');
        });        
    }
};
