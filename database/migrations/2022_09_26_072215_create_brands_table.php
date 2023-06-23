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
            $table->id();
            $table->string('dns')->unique();
            $table->string('name');
            $table->string('theme_css')->nullable();    // json으로 저장
            $table->string('logo_img')->nullable()->comment('로고 이미지');
            $table->string('favicon_img')->nullable()->comment('파비콘 이미지');
            $table->string('passbook_img', 100)->nullable()->comment('통장 사본');
            $table->string('id_img', 100)->nullable()->comment('신분증 사본');
            $table->string('contract_img', 100)->nullable()->comment('계약서 사본');
            $table->string('og_img')->nullable()->comment('오픈 그래프 이미지(1200x630 권장)');
            $table->string('bsin_lic_img', 100)->nullable()->comment('사업자 등록증 사본');
            $table->string('og_description')->nullable()->comment('오픈 그래프 내용');
            $table->string('note')->nullable()->comment('내용');
            $table->string('company_nm')->nullable()->comment('회사명');
            $table->string('pvcy_rep_nm')->nullable()->comment('개인정보 보호 담당자명');
            $table->string('ceo_nm')->nullable()->comment('대표자명');
            $table->string('addr')->nullable()->comment('주소');
            $table->string('business_num')->nullable()->comment('사업자 번호');
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->string('fax_num')->nullable()->comment('팩스 번호');
            $table->string('pv_options', 1000)->default('[]')->comment('페이베리 옵션()');
            $table->tinyInteger('deposit_day')->default(1)->comment('입금일');
            $table->integer('deposit_amount')->default(0)->comment('입금액');
            $table->datetime('last_dpst_at')->default('1970-01-01')->comment('마지막 입금일');
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
