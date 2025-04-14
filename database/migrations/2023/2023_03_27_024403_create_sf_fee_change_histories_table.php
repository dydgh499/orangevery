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
        Schema::create('sf_fee_change_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('bf_sales_id')->nullable()->comment('이전 영업자 FK');
            $table->integer('aft_sales_id')->nullable()->comment('이후 영업자 FK');
            $table->tinyInteger('level')->comment('영업자 등급');
            $table->float('bf_trx_fee',6, 5)->nullable()->comment('이전 거래 수수료');
            $table->float('aft_trx_fee',6, 5)->nullable()->comment('이후 거래 수수료');
            $table->boolean('change_status')->default(false)->comment('변경타입(0=변경 대기, 1=변경 완료)');
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
        Schema::dropIfExists('sf_fee_change_histories');
    }
};
