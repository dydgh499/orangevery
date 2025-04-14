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
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->boolean('is_agency_van')->default(false)->comment('지급대행 모듈 사용여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_vans', function (Blueprint $table) {
            //
        });
    }
};
