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
        Schema::table('cancel_deposits', function (Blueprint $table) {
            $table->unsignedInteger('mcht_settle_id')->nullable()->comment('가맹점 정산 ID')->constrained('settle_histories_merchandises')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancel_deposits', function (Blueprint $table) {
            //
        });
    }
};
