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
            $table->foreignId('user_id')->comment('유저 FK');
            $table->string('contract_type', 100)->comment('담당자 구분(계약, 이메일, 개인전화번호, 이메일)');
            $table->string('contract_officer', 100)->comment('담당자 명');
            $table->string('contract_mobile', 100)->comment('담당자 개인 전화번호');
            $table->string('contract_tel', 100)->comment('담당자 사무실 전화번호');
            $table->string('contract_email', 100)->comment('담당자 담당자 이메일');
            $table->integer('tax_rate')->comment('정산 세율 타입');
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
