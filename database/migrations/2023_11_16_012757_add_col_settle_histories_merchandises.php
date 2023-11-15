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
            $table->integer('cancel_deposit')->default(0)->comment('취소입금합계');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settle_histories_merchandises', function (Blueprint $table) {
            //
        });
    }
};
