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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('pmod_id')->index()->nullable()->comment('결제모듈');
            $table->unsignedMediumInteger('category_id')->nullable()->comment('카테고리 FK')->constrained('categories')->onDelete('SET NULL');
            $table->text('content')->nullable()->comment('상품설명');
        });
        Schema::table('bill_keys', function (Blueprint $table) {
            $table->string('card_num', 255)->default('')->comment('카드번호');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('pmod_id');
            $table->dropColumn('category_id');
            $table->dropColumn('content');
        });
        Schema::table('bill_keys', function (Blueprint $table) {
            $table->dropColumn('card_num');
        });
    }
};
