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
        Schema::table('salesforces', function (Blueprint $table) {
            $table->string('name')->nullable()->comment('전산명');
            $table->string('dns')->nullable()->index();
            $table->string('theme_css')->nullable();    // json으로 저장
            $table->string('logo_img')->nullable()->comment('로고 이미지');
            $table->string('favicon_img')->nullable()->comment('파비콘 이미지');
            $table->string('og_img')->nullable()->comment('오픈 그래프 이미지(1200x630 권장)');
            $table->string('login_img')->nullable()->comment('로그인 배경 이미지');
            $table->string('og_description')->nullable()->comment('오픈 그래프 내용');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesforces', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('dns');
            $table->dropColumn('theme_css');
            $table->dropColumn('logo_img');
            $table->dropColumn('favicon_img');
            $table->dropColumn('og_img');
            $table->dropColumn('login_img');
            $table->dropColumn('og_description');
        });
    }
};
