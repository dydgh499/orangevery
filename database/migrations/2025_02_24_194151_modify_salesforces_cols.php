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
            $table->renameColumn('is_able_modify_mcht', 'auth_level');
        });
        Schema::table('salesforces', function (Blueprint $table) {
            $table->tinyInteger('auth_level')->comment('영업라인 권한(0=권한없음, 1=추가만가능, 2=추가/수정/삭제 가능)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
            $table->renameColumn('auth_level', 'is_able_modify_mcht');
        });
    }
};
