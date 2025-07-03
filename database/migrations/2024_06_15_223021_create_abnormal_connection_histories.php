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
        Schema::create('abnormal_connection_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('connection_type')->comment('접속 타입');
            $table->string('action')->default('')->comment('조치사항');
            $table->string('target_key')->default('')->comment('대상');
            $table->tinyInteger('target_level')->default(0)->comment('대상등급');
            $table->string('target_value', 4000)->default('')->comment('값');
            $table->string('request_ip', 50)->default('')->comment('접속 IP');
            $table->string('request_detail', 500)->default('')->comment('접속 상세정보');
            $table->string('mobile_type', 10)->default('')->comment('이동통신 여부');
            $table->string('comment')->default('')->comment('메모사항');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abnormal_connection_histories');
    }
};
