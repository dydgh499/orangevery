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
        Schema::create('exception_work_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('oper_id')->nullable()->comment('운영자 FK')->constrained('operators')->onDelete('SET NULL');
            $table->timestamp('work_s_at')->nullable()->comment('작업시작시간');
            $table->timestamp('work_e_at')->nullable()->comment('작업종료시간');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exception_work_times');
    }
};
