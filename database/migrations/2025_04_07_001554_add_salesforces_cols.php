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
            $table->string('sales_sub_name', 50)->nullable()->comment('사업자명');
            $table->string('email', 50)->default('')->nullable()->comment('이메일');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
            $table->dropColumn('sales_sub_name');
            $table->dropColumn('email');
        });
    }
};
