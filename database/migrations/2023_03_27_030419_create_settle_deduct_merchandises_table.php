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
        Schema::create('settle_deduct_merchandises', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('amount')->default(0)->comment('차감금액');
            $table->date('deduct_dt')->nullable()->comment('적용일');
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
        Schema::dropIfExists('settle_deduct_merchandises');
    }
};
