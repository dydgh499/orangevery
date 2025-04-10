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
        Schema::table('settle_histories_salesforces', function (Blueprint $table) {
            $table->bigInteger('total_amount')->change();
            $table->bigInteger('trx_amount')->change();
            $table->bigInteger('cxl_amount')->change();
            $table->bigInteger('appr_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settle_histories_salesforces', function (Blueprint $table) {
            
        });
    }
};
