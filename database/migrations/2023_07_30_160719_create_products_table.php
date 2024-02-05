<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->string('product_name')->default('')->comment('상품명');
            $table->integer('product_amount')->default(0)->comment('상품가격');
            $table->string('product_img')->default('')->comment('상품이미지');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
