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
            $table->float('mcht_batch_fee', 6, 5)->default(0)->comment('가맹점 일괄적용 수수료');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
        });
    }
};
