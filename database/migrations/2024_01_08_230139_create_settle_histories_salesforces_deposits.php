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
        Schema::create('settle_histories_salesforces_deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('settle_hist_sales_id')->nullable()->comment('영업점 정산 이력 ID');
            $table->foreign('settle_hist_sales_id', 'shsd_shsales_id_fk')
                ->references('id')
                ->on('settle_histories_salesforces')
                ->onDelete('SET NULL');

            $table->string('trans_seq_num', 50)->nullable()->index()->comment('요청 ID');
            $table->integer('request_type')->nullable()->comment('요청 타입');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->string('message', 50)->nullable()->comment('내용');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settle_histories_salesforces_deposits');
    }
};
