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
            $table->string('note', 100)->nullable()->comment('메모');
            $table->integer('sales5_id')->nullable()->comment('지사');
            $table->float('sales5_fee', 6, 5)->default(0)->comment('지사 수수료');
            //
            $table->integer('sales4_id')->nullable()->comment('하위 지사');
            $table->float('sales4_fee', 6, 5)->default(0)->comment('하위 지사 수수료');
            //
            $table->integer('sales3_id')->nullable()->comment('총판');
            $table->float('sales3_fee', 6, 5)->default(0)->comment('총판 수수료');
            //
            $table->integer('sales2_id')->nullable()->comment('하위 총판');
            $table->float('sales2_fee', 6, 5)->default(0)->comment('하위 총판 수수료');
            //
            $table->integer('sales1_id')->nullable()->comment('대리점');
            $table->float('sales1_fee', 6, 5)->default(0)->comment('대리점 수수료');
            //
            $table->integer('sales0_id')->nullable()->comment('하위 대리점');
            $table->float('sales0_fee', 6, 5)->default(0)->comment('하위 대리점 수수료');
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
