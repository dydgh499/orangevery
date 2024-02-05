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
        Schema::create('danger_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('mcht_id')->nullable()->comment('사용 가맹점 ID')->constrained('merchandises')->onDelete('SET NULL');
            $table->integer('trans_id')->comment('거래 ID');
            $table->tinyInteger('module_type')->default(0)->comment('거래타입');
            $table->tinyInteger('danger_type')->default(0)->comment('이상 거래 타입');
            $table->boolean('is_checked')->default(false)->comment('확인 여부');
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
        Schema::dropIfExists('orders');
    }
};
