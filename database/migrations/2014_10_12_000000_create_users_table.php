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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id')->default(0)->comment('GID');
            $table->string('phone', 30)->comment('ID(연락처)');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('use_verified_phone')->default(1)->comment('휴대폰 인증 활성화(2차 인증)');
            $table->boolean('use_verified_email')->default(1)->comment('이메일 인증 활성화(3차 인증)');
            $table->string('password');
            $table->string('avatar')->comment('회원 이미지');
            $table->integer('level')->default(0)->comment('유저 레벨(0=가맹점, 10=대리점, 20=총판, 30=지사, 40=본사, 50=개발사)');
            $table->float('fees_rate')->default(0)->comment('수수료율');
            //---------------------------------------------
            $table->string('acct_holder')->nullable()->comment('예금주');
            $table->string('acct_num')->nullable()->comment('예금계좌번호');
            $table->string('acct_bank_nm')->nullable()->comment('은행명');
            $table->string('acct_bank_cd')->nullable()->comment('은행코드');
            $table->string('bsin_img')->nullable()->comment('통장 사본');
            $table->string('id_img')->nullable()->comment('신분증 사본');
            $table->string('contract_img')->nullable()->comment('계약서 사본');
            //---------------------------------------------
            $table->string('sector')->nullable()->comment('업종');
            $table->string('rep_name', 100)->nullable()->comment('대표자 명');
            $table->string('business_nm', 100)->nullable()->comment('사업지 명');
            $table->string('business_addr', 200)->nullable()->comment('사업지 주소');
            $table->string('business_num', 100)->nullable()->comment('사업자 번호');
            $table->string('resident_num', 100)->nullable()->comment('주민등록번호');
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
        Schema::dropIfExists('users');
    }
};
