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
        Schema::create('before_brand_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->string('company_name')->nullable()->comment('회사명');
            $table->string('rep_name')->nullable()->comment('대표자명');
            $table->string('addr')->nullable()->comment('주소');
            $table->string('business_num')->nullable()->comment('사업자등록번호');
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->date('apply_s_dt')->nullable()->comment('적용 시작일');
            $table->date('apply_e_dt')->nullable()->comment('적용 종료일');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('before_brand_infos', function (Blueprint $table) {
            //
        });
    }
};
