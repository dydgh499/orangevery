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
        Schema::rename('operator_histories', 'activity_histories');

        Schema::table('activity_histories', function (Blueprint $table) {
            $table->renameColumn('oper_id', 'user_id');
            $table->tinyInteger('level')->default(0)->comment('유저 LEVEL');
            $table->unsignedInteger('target_id')->nullable()->comment('대상 PK');
        });
        Schema::table('activity_histories', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('유저 PK')->change();       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('activity_histories', 'activity_histories');
        Schema::table('operator_histories', function (Blueprint $table) {
            $table->renameColumn('user_id', 'oper_id');
            $table->dropColumn('level');
        });
        Schema::table('activity_histories', function (Blueprint $table) {
            $table->unsignedInteger('oper_id')->comment('운영자 PK')->change();
        });
    }
};
