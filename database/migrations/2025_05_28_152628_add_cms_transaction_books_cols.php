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
        
        Schema::table('cms_transaction_books', function (Blueprint $table) {
            $table->string('trans_seq_num', 50)->nullable()->comment('거래번호(출금 전용)');            
        });
        
        Schema::table('cms_transaction_books', function (Blueprint $table) {
            $table->unique(['brand_id', 'trans_seq_num', 'is_withdraw'], 'duplicate_trx_id_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_transaction_books', function (Blueprint $table) {
            $table->dropColumn('trans_seq_num');
        });
    }
};
