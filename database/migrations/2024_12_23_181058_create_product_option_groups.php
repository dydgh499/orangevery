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
        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('product_id')->nullable()->comment('상품 FK')->constrained('products')->onDelete('SET NULL');
            $table->string('group_name', 30)->nullable()->comment('그룹 이름');
            $table->boolean('is_able_duplicate')->default(false)->comment('그룹 중복생성 여부(0=불가, 1=가능)');
            $table->boolean('is_able_count')->default(false)->comment('옵션 수량선택 여부(0=불가, 1=가능)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_option_groups');
    }
};
