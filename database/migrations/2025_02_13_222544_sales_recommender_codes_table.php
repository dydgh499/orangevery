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
        Schema::create('sales_recommender_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id')->nullable()->comment('영업점 FK')->constrained('salesforces')->onDelete('SET NULL');
            $table->float('mcht_fee', 6, 5)->default(0)->comment('가맹점 수수료');
            $table->string('recommend_code', 20)->unique()->nullable()->comment('추천인코드');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_recommender_codes');
    }
};
