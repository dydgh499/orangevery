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
        Schema::create('holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');            
            $table->string('rest_name', 50)->nullable()->comment('공휴일 명칭');
            $table->date('rest_dt')->nullable()->comment('공휴일 날짜');
            $table->tinyInteger('rest_type', false, true)->default(1)->comment('공휴일 분류(0=직접등록, 1=공공기관 휴일, 2=기념일)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
