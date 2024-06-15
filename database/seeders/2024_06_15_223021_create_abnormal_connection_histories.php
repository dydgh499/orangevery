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
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('connection_type')->comment('접속 타입');
            $table->string('target_key')->default('')->comment('대상');
            $table->string('target_value', 2000)->default('')->comment('값');
            $table->string('request_ip', 20)->default('')->comment('접속 IP');
            $table->string('request_detail', 20)->default('')->comment('접속 IP');
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
