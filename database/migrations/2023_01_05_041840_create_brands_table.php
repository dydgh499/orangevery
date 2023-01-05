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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('dns')->unique();
            $table->string('name')->comment('서비스명');
            $table->string('logo_img')->nullable();
            $table->string('favicon_img')->nullable();
            $table->string('company_nm')->nullable()->comment('회사명');
            $table->string('pvcy_rep_nm')->nullable()->comment('개인정보 보호 담당자명');
            $table->string('ceo_nm')->nullable()->comment('대표자명');
            $table->string('company_addr')->nullable()->comment('주소');
            $table->string('business_num')->nullable()->comment('사업자 번호');
            $table->string('phone_num')->nullable()->comment('휴대폰 번호');
            $table->string('fax_num')->nullable()->comment('팩스 번호');
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
        Schema::dropIfExists('brands');
    }
};
