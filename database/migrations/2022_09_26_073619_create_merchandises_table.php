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
            $table->unsignedMediumInteger('id', true);
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            //
            $table->unsignedMediumInteger('sales5_id')->nullable()->comment('지사');
            $table->float('sales5_fee', 6, 5)->default(0)->comment('지사 수수료');
            //
            $table->unsignedMediumInteger('sales4_id')->nullable()->comment('하위 지사');
            $table->float('sales4_fee', 6, 5)->default(0)->comment('하위 지사 수수료');
            //
            $table->unsignedMediumInteger('sales3_id')->nullable()->comment('총판');
            $table->float('sales3_fee', 6, 5)->default(0)->comment('총판 수수료');
            //
            $table->unsignedMediumInteger('sales2_id')->nullable()->comment('하위 총판');
            $table->float('sales2_fee', 6, 5)->default(0)->comment('하위 총판 수수료');
            //
            $table->unsignedMediumInteger('sales1_id')->nullable()->comment('대리점');
            $table->float('sales1_fee', 6, 5)->default(0)->comment('대리점 수수료');
            //
            $table->unsignedMediumInteger('sales0_id')->nullable()->comment('하위 대리점');
            $table->float('sales0_fee', 6, 5)->default(0)->comment('하위 대리점 거래 수수료');
            //

            $table->string('user_name', 30)->index()->comment('ID');
            $table->string('user_pw', 100)->comment('PW');
            $table->string('nick_name', 30)->nullable()->comment('대표자명');
            
            $table->string('profile_img')->nullable()->comment('프로필 이미지');
            $table->string('mcht_name', 100)->comment('가맹점명');
            $table->string('addr', 200)->nullable()->comment('가맹점 주소');
            //
            $table->float('hold_fee', 6, 5)->default(0)->comment('보유금액 수수료');
            $table->float('trx_fee', 6, 5)->default(0)->comment('거래 수수료');
            //
            $table->string('phone_num',20)->nullable()->comment('휴대폰 번호');
            $table->string('resident_num', 20)->nullable()->comment('주민등록번호');
            $table->string('business_num', 20)->nullable()->comment('사업자등록번호');
            $table->string('sector', 50)->nullable()->comment('업종');
            //
            $table->string('passbook_img', 150)->nullable()->comment('통장 사본');
            $table->string('id_img', 150)->nullable()->comment('신분증 사본');
            $table->string('contract_img', 150)->nullable()->comment('계약서 사본');
            $table->string('bsin_lic_img', 150)->nullable()->comment('사업자 등록증 사본');
            $table->string('acct_num', 20)->nullable()->comment('계좌번호');
            $table->string('acct_name', 50)->nullable()->comment('예금주명');
            $table->string('acct_bank_name', 30)->nullable()->comment('입금은행명');
            $table->string('acct_bank_code', 3)->nullable()->comment('은행코드');
            // option
            $table->integer('custom_id')->nullable()->comment('커스텀 필터');
            $table->boolean('enabled')->default(true)->comment('사용 여부(0=미사용, 1=사용)');
            $table->boolean('use_saleslip_prov')->default(true)->comment('매출전표 공급자 사용 여부(0=사용, 1=본사)');
            $table->boolean('use_saleslip_sell')->default(false)->comment('매출전표 판매자 사용 여부(0=사용, 1=본사)');
            $table->boolean('use_noti')->default(false)->comment('노티 사용여부');
            $table->boolean('is_show_fee')->default(false)->comment('수수료율 노출여부');

            $table->boolean('use_regular_card')->default(false)->comment('정기 카드 사용여부(단골고객)');
            $table->boolean('use_collect_withdraw')->default(false)->comment('모아서 출금여부');
            $table->boolean('use_multiple_hand_pay')->default(false)->comment('다중결제기능 사용여부');
            $table->boolean('use_pay_verification_mobile')->default(false)->comment('결제전 휴대폰 인증 사용여부');            
            
            $table->mediumInteger('collect_withdraw_fee')->default(0)->comment('모아서 출금 수수료(가맹점)');
            $table->mediumInteger('withdraw_fee')->default(0)->comment('출금 수수료');
            $table->tinyInteger('tax_category_type')->default(0)->comment('면세사업자 파라미터(0=면세, 1=과세, 2=복합)');
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
        Schema::dropIfExists('merchandises');
    }
}
