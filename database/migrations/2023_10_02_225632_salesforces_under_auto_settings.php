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
        Schema::create('salesforces_under_auto_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('sales_id')->nullable()->comment('영업점 FK')->constrained('salesforces')->onDelete('SET NULL');
            $table->float('sales_fee', 6, 5)->default(0)->comment('영업점 수수료');
            $table->string('note', 100)->nullable()->comment('메모');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesforces_under_auto_settings');
    }
};
