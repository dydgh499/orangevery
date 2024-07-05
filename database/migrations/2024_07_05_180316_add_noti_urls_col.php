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
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->integer('pmod_id')->nullable()->default(-1)->comment('사용 결제모듈 ID')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noti_urls', function (Blueprint $table) {
            //
        });
    }
};
