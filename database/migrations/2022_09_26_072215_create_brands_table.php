<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //app_order_flag, app_gift_flag, coupon_flag
        Schema::create('brands', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->string('dns')->unique();
            $table->string('name')->nullable()->comment('운영사 명');
            $table->string('theme_css')->nullable();    // json으로 저장
            $table->string('logo_img')->nullable()->comment('로고 이미지');
            $table->string('favicon_img', 150)->nullable()->comment('파비콘 이미지');
            $table->string('passbook_img', 150)->nullable()->comment('통장 사본');
            $table->string('id_img', 150)->nullable()->comment('신분증 사본');
            $table->string('contract_img', 150)->nullable()->comment('계약서 사본');
            $table->string('og_img', 150)->nullable()->comment('오픈 그래프 이미지(1200x630 권장)');
            $table->string('bsin_lic_img', 150)->nullable()->comment('사업자 등록증 사본');
            $table->string('login_img', 150)->nullable()->comment('로그인 배경 이미지');
            $table->string('og_description')->nullable()->comment('오픈 그래프 내용');
            $table->string('note')->nullable()->comment('내용');
            $table->string('company_name')->nullable()->comment('회사명');
            $table->string('ceo_name')->nullable()->comment('대표자명');
            $table->string('addr')->nullable()->comment('주소');
            $table->string('business_num')->nullable()->comment('사업자등록번호');
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->string('fax_num')->nullable()->comment('팩스 번호');
            $table->string('ov_options', 3000)->default('[]')->comment('오렌지베리 옵션()');
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
        Schema::dropIfExists('services');
    }
}
