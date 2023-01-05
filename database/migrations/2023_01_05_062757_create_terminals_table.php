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
        Schema::create('terminals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcht_id')->comment('가맹점 FK');
            $table->string('serial_num')->unique()->comment('시리얼 번호');
            $table->string('tid')->unique()->comment('터미널 ID');
            $table->integer('terminal_type')->comment('터미널 타입');
            $table->date('ship_out_dt')->comment('출고일');
            $table->integer('ship_out_stat')->nullable()->comment('출고상태');
            $table->string('note')->nullable()->comment('메모');
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
        Schema::dropIfExists('terminals');
    }
};
