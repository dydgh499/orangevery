<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            //
            $table->integer('sales5_id')->default(0)->comment('지사 수수료');
            $table->float('sales5_fee', 6, 4)->default(0)->comment('지사 수수료');
            //
            $table->integer('sales4_id')->default(0)->comment('하위 지사 수수료');
            $table->float('sales4_fee', 6, 4)->default(0)->comment('하위 지사 수수료');
            //
            $table->integer('sales3_id')->default(0)->comment('총판 수수료');
            $table->float('sales3_fee', 6, 4)->default(0)->comment('총판 수수료');
            //
            $table->integer('sales2_id')->default(0)->comment('하위 총판 수수료');
            $table->float('sales2_fee', 6, 4)->default(0)->comment('하위 총판 수수료');
            //
            $table->integer('sales1_id')->default(0)->comment('대리점 수수료');
            $table->float('sales1_fee', 6, 4)->default(0)->comment('대리점 수수료');
            //
            $table->integer('sales0_id')->default(0)->comment('하위 대리점 수수료');
            $table->float('sales0_fee', 6, 4)->default(0)->comment('하위 대리점 거래 수수료');
            //

            $table->string('user_name', 30)->index()->comment('ID');
            $table->string('user_pw', 100)->comment('PW');
            $table->string('nick_name', 30)->nullable()->comment('유저 닉네임');
            $table->string('profile_img')->nullable()->comment('프로필 이미지');
            $table->string('mcht_name', 100)->comment('가맹점명');
            $table->string('addr', 200)->nullable()->comment('가맹점 주소');
            //
            $table->float('hold_fee', 6, 4)->default(0)->comment('보유금액 수수료');
            $table->float('trx_fee', 6, 4)->default(0)->comment('거래 수수료');
            //
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->string('resident_num', 20)->nullable()->comment('주민등록번호');
            $table->string('business_num', 20)->nullable()->comment('사업자번호');
            $table->string('sector', 20)->nullable()->comment('업종');
            //
            $table->string('passbook_img', 100)->nullable()->comment('통장 사본');
            $table->string('id_img', 100)->nullable()->comment('신분증 사본');
            $table->string('contract_img', 700)->nullable()->comment('계약서 사본');
            $table->string('bsin_lic_img', 100)->nullable()->comment('사업자 등록증 사본');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('예금주명');
            $table->string('acct_bank_name', 30)->nullable()->comment('입금은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            // option
            $table->integer('custom_id')->nullable()->comment('커스텀 필터');
            $table->boolean('enabled')->default(true)->comment('사용 여부(0=미사용, 1=사용)');
            $table->boolean('use_saleslip_prov')->default(false)->comment('매출전표 공급자 사용 여부(0=사용, 1=본사)');
            $table->boolean('use_saleslip_sell')->default(false)->comment('매출전표 판매자 사용 여부(0=사용, 1=본사)');
            $table->boolean('show_fee_easy_view')->default(false)->comment('간편보기 수수료율 노출여부');

            //$table->string('pv_options', 1000)->default('[]')->comment('가맹점 옵션'); -> 삭제
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
        Schema::dropIfExists('merchandises');
    }
}
