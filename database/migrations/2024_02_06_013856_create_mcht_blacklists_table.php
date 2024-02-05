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
        Schema::create('mcht_blacklists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedSmallInteger('block_type')->comment('필터타입(0=상호, 1=대표자명, 2=사업자번호, 3=휴대폰번호, 4=주민번호, 5=주소)');
            $table->string('block_content', 50)->default('')->comment('차단내용');
            $table->string('block_reason')->default('')->comment('차단사유');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcht_blacklists');
    }
};
