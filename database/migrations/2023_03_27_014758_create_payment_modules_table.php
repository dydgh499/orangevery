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
        Schema::create('payment_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('oper_id')->nullable()->comment('운영자 ID');
            $table->integer('pg_id')->nullable()->comment('결제대행사 id');
            $table->integer('ps_id')->nullable()->comment('결제대행사 수수료율 id');
            $table->tinyInteger('module_type')->default(0)->comment('모듈 타입(0=장비, 1=수기, 2=인증, 3=간편, 4=빌링)');
            $table->string('api_key', 100)->default('')->comment('API KEY');
            $table->string('sub_key', 100)->default('')->comment('SUB KEY');
            $table->string('mid', 50)->default('')->comment('mid');
            $table->string('tid', 50)->default('')->comment('tid');
            $table->boolean('is_old_auth')->default(false)->comment('수기결제 타입(0=비인증, 1=구인증)');

            $table->string('note', 200)->nullable()->default('')->comment('별칭');
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
        Schema::dropIfExists('payment_modules');
    }
};
