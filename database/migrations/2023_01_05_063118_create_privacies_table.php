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
        Schema::create('privacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('유저 FK');
            $table->string('acct_holder')->comment('예금주');
            $table->string('acct_num')->comment('예금계좌번호');
            $table->string('acct_bank_nm')->comment('은행명');
            $table->string('acct_bank_cd')->comment('은행코드');
            $table->string('bsin_img')->comment('통장 사본');
            $table->string('id_img')->comment('신분증 사본');
            $table->string('contract_img')->comment('계약서 사본');
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
        Schema::dropIfExists('privacies');
    }
};
