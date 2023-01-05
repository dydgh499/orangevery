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
            $table->string('mcht_nm', 100)->comment('가맹점명');
            $table->string('mid', 100)->comment('MID');
            $table->string('api_key', 100)->comment('API KEY');
            $table->string('secret_key', 100)->comment('secret KEY');
            $table->string('mcht_addr', 200)->nullable()->comment('가맹점 주소');
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
};
