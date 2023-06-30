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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->string('tid', 50)->default('')->comment('tid');
            $table->string('cust_name', 50)->default('')->comment('고객명');
            $table->date('appr_dt')->default('1970-01-01')->comment('승인일');
            $table->string('appr_num', 9)->comment('승인번호');
            $table->string('phone_num', 20)->default('')->comment('휴대폰 번호');
            $table->string('hand_cust_name', 50)->default('')->comment('수기작성성함');
            $table->string('hand_phone_num', 20)->default('')->comment('수기작성연락처');
            $table->string('issuer')->default('')->comment('발급사');
            $table->integer('pg_id')->default(0)->comment('PG사');
            $table->tinyInteger('type')->default(0)->comment('민원타입');
            $table->string('entry_path', 50)->default('')->comment('인입경로');
            $table->boolean('is_deposit')->default(false)->comment('입금상태');
            $table->string('note', 255)->default('')->comment('내용');
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
        Schema::dropIfExists('complaints');
    }
};
