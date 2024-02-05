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
        Schema::create('sf_fee_apply_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('sales_id')->nullable()->comment('영업점 FK')->constrained('salesforces')->onDelete('SET NULL');
            $table->float('trx_fee', 6, 5)->nullable()->comment('적용 거래 수수료');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sf_fee_apply_histories');
    }
};
