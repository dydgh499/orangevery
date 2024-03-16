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
        Schema::table('operator_histories', function (Blueprint $table) {
            $table->string('before_history_detail', 1500)->default('')->nullable()->comment('이전 상세이력');
            $table->renameColumn('history_detail', 'after_history_detail');
        });
        Schema::table('operator_histories', function (Blueprint $table) {
            $table->string('after_history_detail', 1500)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_histories', function (Blueprint $table) {
            //
        });
    }
};
