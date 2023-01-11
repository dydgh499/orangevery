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
        Schema::create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('유저 FK'); 
            $table->integer('withdraw_id')->default(0);
            $table->integer('pg_id')->default(0);
            $table->integer('pg_section_id')->default(0);
            $table->integer('abnormal_transaction_limit')->default(5000000)->comment('이상거래 한도');
            $table->string('mid', 100)->comment('MID');
            $table->string('api_key', 100)->comment('API KEY(자체 생성)');
            $table->string('secret_key', 100)->comment('secret KEY(자체 생성)');
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
};
