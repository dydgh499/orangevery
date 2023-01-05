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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('유저 FK');
            $table->string('title', 100)->comment('제목');
            $table->string('location')->comment('위치');
            $table->string('event_url')->comment('event url');
            $table->text('description')->comment('내용');
            $table->date('start_dt')->comment('가맹점명');
            $table->date('end_dt', 100)->comment('가맹점명');
            $table->tinyInteger('is_all_day')->default(0)->comment('하루종일');
            $table->tinyInteger('event_type')->default(0)->comment('이벤트 타입(0=퍼스널,1=비즈니스,2=가족,3=기념일,4=기타)');
            $table->timestamps();
        });

        //foregin key
        // ----------------- realiation brand_id ----------------
        Schema::table('users', function($table) {
            $table->foreign('brand_id')->references('id')->on('brands');
        });
        Schema::table('notices', function($table) {
            $table->foreign('brand_id')->references('id')->on('brands');
        });
        // ----------------- realiation user_id ----------------
        Schema::table('merchandises', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('privacies', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('calendar_events', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        // ----------------- realiation mcht_id ----------------
        Schema::table('terminals', function($table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises');
        });
        Schema::table('transactions', function($table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises');
        });
        // ----------------- realiation notice_id ----------------
        Schema::table('comments', function($table) {
            $table->foreign('notice_id')->references('id')->on('notices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
};
