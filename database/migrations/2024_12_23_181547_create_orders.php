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
        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 ID');
            $table->unsignedInteger('trans_id')->nullable()->comment('거래 FK')->constrained('transactions')->onDelete('SET NULL');
            $table->string('addr')->nullable()->comment('배송지');
            $table->string('detail_addr')->nullable()->comment('배송지 상세주소');
            $table->string('option_groups', 600)->default('{}')->comment('옵션 그룹정보');
            $table->string('note')->nullable()->comment('메모사항');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
