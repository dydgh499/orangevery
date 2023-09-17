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
        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('is_use_different_settlement')->default(false)->comment('차액정산 사용여부');
            $table->string('rep_mcht_id', 20)->default('')->comment('상위 대표 가맹점 ID');
            $table->tinyInteger('above_pg_type')->nullable()->comment('상위 PG사 타입(1,2,3,4,5 ...)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('is_use_different_settlement');
            $table->dropColumn('rep_mcht_id');
            $table->dropColumn('above_pg_type');
            //
        });
    }
};
