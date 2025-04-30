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
        Schema::table('settle_histories_merchandises', function (Blueprint $table) {
            $table->unsignedMediumInteger('appr_count')->nullable()->comment('승인 건수');
            $table->unsignedMediumInteger('cxl_count')->nullable()->comment('취소 건수');
        });
        Schema::table('settle_histories_salesforces', function (Blueprint $table) {
            $table->unsignedMediumInteger('appr_count')->nullable()->comment('승인 건수');
            $table->unsignedMediumInteger('cxl_count')->nullable()->comment('취소 건수');
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
