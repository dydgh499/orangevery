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
        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->dropForeign(['mcht_id']); // 외래 키 삭제
        });
        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->renameColumn('mcht_id', 'user_id');
            $table->unsignedSmallInteger('withdraw_fee')->default(0)->comment('출금 수수료');
            $table->tinyInteger('level')->default(0)->comment('유저 LEVEL');
        });
        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('유저 PK')->change();    
            $table->integer('balance')->change();    
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->renameColumn('user_id', 'mcht_id');
            $table->tinyInteger('level')->default(0)->comment('유저 LEVEL');
        });
        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises')->onDelete('SET NULL');
        });
    }
};
