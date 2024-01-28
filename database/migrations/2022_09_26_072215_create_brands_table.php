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
            $table->string('pv_options', 2000)->default('[]')->comment('페이베리 옵션()');
            $table->tinyInteger('deposit_day')->default(1)->comment('입금일');
            $table->integer('deposit_amount')->default(0)->comment('입금액');
            $table->datetime('last_dpst_at')->default('1970-01-01')->comment('마지막 입금일');
            $table->tinyInteger('is_transfer')->default(0)->comment('전산 이전 여부');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');            
            $table->boolean('use_different_settlement')->default(false)->comment('차액정산 사용여부');
            $table->string('rep_mid', 20)->default('')->comment('상위 대표 가맹점 ID');
            $table->tinyInteger('rep_pg_type')->nullable()->comment('상위 PG사 타입(1,2,3,4,5 ...)');            
            $table->float('dev_fee', 6, 5)->default(0)->comment('개발사 수수료');
            $table->tinyInteger('dev_settle_type')->default(0)->comment('수수료 계산 타입(0=본사 이익 대비 %, 1=거래금액 대비 %)');
            $table->integer('extra_deposit_amount')->default(0)->comment('부가 입금 금액');
            $table->integer('curr_deposit_amount')->default(0)->comment('현재 입금 금액(월단위)');
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
