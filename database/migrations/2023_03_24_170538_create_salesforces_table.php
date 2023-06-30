<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesforces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('level')->unsigned()->default(0)->comment('(13,15,17,20,25,30)등급');
            $table->string('user_name', 30)->index()->comment('ID');
            $table->string('user_pw', 100)->comment('PW');
            $table->string('nick_name', 30)->nullable()->comment('유저명');
            $table->string('sales_name', 30)->nullable()->comment('영업점명');
            $table->string('addr', 150)->nullable()->comment('영업점 주소');
            $table->string('profile_img')->nullable()->comment('프로필 이미지');
            //
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->string('resident_num', 20)->nullable()->comment('주민등록번호');
            $table->string('business_num', 20)->nullable()->comment('사업자번호');
            $table->string('sector', 20)->nullable()->comment('업종');
            //
            $table->string('passbook_img', 100)->nullable()->comment('통장 사본');
            $table->string('id_img', 100)->nullable()->comment('신분증 사본');
            $table->string('contract_img', 100)->nullable()->comment('계약서 사본');
            $table->string('bsin_lic_img', 100)->nullable()->comment('사업자 등록증 사본');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('예금주명');
            $table->string('acct_bank_name', 30)->nullable()->comment('입금은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            $table->tinyInteger('settle_tax_type')->default(0)->comment('정산 세율(0, 3.3, 10, 10+3.3)');
            $table->tinyInteger('settle_cycle')->default(0)->comment('정산 주기(1일, 일주일, 2주일, 한달(30일))');
            $table->tinyInteger('settle_day')->nullable()->comment('정산 요일(일,월,화,수,목~)');
            $table->date('last_settle_dt')->nullable()->comment('마지막 정산일');
            //
            $table->boolean('view_type')->default(false)->comment('뷰 타입(0=간편보기, 1=상세보기)');
            $table->string('note', 100)->nullable()->comment('메모');
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
        Schema::dropIfExists('salesforces');
    }
};
