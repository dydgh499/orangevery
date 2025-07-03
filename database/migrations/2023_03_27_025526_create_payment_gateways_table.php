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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('oper_id')->nullable()->comment('운영자 ID');
            $table->tinyInteger('pg_type')->comment('결제대행사명(1,2,3,4,5 ...)');
            $table->string('pg_name')->comment('결제대행사명');
            $table->string('rep_name')->comment('대표자명');
            $table->string('company_name')->comment('회사명');
            $table->string('business_num', 20)->nullable()->comment('사업자등록번호');
            $table->string('phone_num', 20)->default('')->comment('휴대폰 번호');
            $table->string('addr', 200)->nullable()->comment('사업지 주소');
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
        Schema::dropIfExists('payment_gateways');
    }
};
