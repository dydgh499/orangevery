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
        Schema::create('operator_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('oper_id')->nullable()->comment('운영자 FK')->constrained('operators')->onDelete('SET NULL');
            $table->tinyInteger('history_type')->default(0)->comment('이력 타입(0=추가, 1=수정, 2=삭제, 3=조회)');
            $table->string('history_target', 50)->nullable()->comment('활동종류');
            $table->string('history_title', 50)->nullable()->comment('활동대상');            
            $table->string('before_history_detail', 3000)->nullable()->comment('이전 내용');          
            $table->string('after_history_detail', 3000)->nullable()->comment('변경 내용');            
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
        Schema::dropIfExists('operator_histories');
    }
};
