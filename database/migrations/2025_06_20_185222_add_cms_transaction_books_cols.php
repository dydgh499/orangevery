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
            $table->string('message', 100)->nullable()->default('')->comment('출금결과메세지');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_transaction_books', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }
};
