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
        Schema::create('fees_rate_modify_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('유저 FK');
            $table->string('reason', 100)->comment('변경 이유');
            $table->datetime('apply_start_dttm')->comment('적용 시작일, 시간');
            $table->boolean('is_apply')->comment('적용 여부');
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
        Schema::dropIfExists('fees_rate_modify_logs');
    }
};
