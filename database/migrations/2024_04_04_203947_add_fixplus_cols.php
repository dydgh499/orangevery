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
            $table->unsignedSmallInteger('parent_id')->nullable()->comment('부모 영업점 FK')->constrained('salesforces')->onDelete('SET NULL');
            $table->float('sales_fee', 6, 5)->default(0)->comment('영업점 수수료');
        });
        Schema::table('regular_credit_cards', function (Blueprint $table) {
            $table->string('yymm', 5)->nullable()->comment('유효기간');
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
