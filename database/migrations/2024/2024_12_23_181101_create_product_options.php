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
        Schema::create('product_options', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('group_id')->default(0)->comment('옵션 그룹 ID')->constrained('groups')->onDelete('SET NULL');
            $table->string('option_name', 50)->nullable()->comment('옵션 이름');
            $table->integer('option_price')->nullable()->default(0)->comment('옵션 금액');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
