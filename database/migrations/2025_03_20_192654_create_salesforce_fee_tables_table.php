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
        Schema::create('salesforce_fee_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedMediumInteger('sales5_id')->nullable()->comment('지사');
            $table->unsignedMediumInteger('sales4_id')->nullable()->comment('하위지사');
            $table->unsignedMediumInteger('sales3_id')->nullable()->comment('총판');
            $table->unsignedMediumInteger('sales2_id')->nullable()->comment('하위총판');
            $table->unsignedMediumInteger('sales1_id')->nullable()->comment('대리점');
            $table->float('sales5_fee', 7, 5)->default(0)->comment('지사 수수료');
            $table->float('sales4_fee', 7, 5)->default(0)->comment('하위지사 수수료');
            $table->float('sales3_fee', 7, 5)->default(0)->comment('총판 수수료');
            $table->float('sales2_fee', 7, 5)->default(0)->comment('하위총판 수수료');
            $table->float('sales1_fee', 7, 5)->default(0)->comment('대리점 수수료');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesforce_fee_tables');
    }
};
