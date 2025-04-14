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
            $table->string('mcht_name', 100)->default('')->comment('상호');
            $table->string('nick_name', 20)->default('')->comment('대표자명');
            $table->string('phone_num', 20)->default('')->comment('휴대폰번호');
            $table->string('business_num', 20)->default('')->comment('사업자번호');
            $table->string('resident_num', 20)->default('')->comment('주민번호');
            $table->string('addr', 200)->default('')->comment('주소');
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
