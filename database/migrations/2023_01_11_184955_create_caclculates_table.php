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
        Schema::create('caclculates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('유저 FK'); 
            $table->string('acct_holder')->comment('예금주');
            $table->string('acct_num')->comment('예금계좌번호');
            $table->string('acct_bank_nm')->comment('은행명');
            $table->string('acct_bank_cd')->comment('은행코드');
            $table->integer('calc_profit')->default(0)->comment('정산금');
            $table->date('calc_dt')->comment('정산일');
            $table->date('payment_dt')->comment('지급일');
            $table->date('apply_s_dt')->comment('적용 시작일');
            $table->date('apply_e_dt')->comment('적용 종료일');
            $table->integer('status')->default(0)->comment('정산 상태(0=미지급, 5=지급 완료)');
            $table->timestamps();
        });
        
        //foregin key
        // ----------------- realiation user_id ----------------
        Schema::table('merchandises', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('salesforces', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('fees_rate_modify_logs', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('calendar_events', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('caclculates', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        // ----------------- realiation mcht_id ----------------
        Schema::table('terminals', function($table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises');
        });
        Schema::table('transactions', function($table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises');
        });
        Schema::table('services', function($table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises');
        });
        Schema::table('trans_notifications', function($table) {
            $table->foreign('mcht_id')->references('id')->on('merchandises');
        });
        // ----------------- realiation notice_id ----------------
        Schema::table('comments', function($table) {
            $table->foreign('notice_id')->references('id')->on('notices');
        });
        // ----------------- realiation trans_noti_id ----------------
        Schema::table('trans_notification_logs', function($table) {
            $table->foreign('trans_noti_id')->references('id')->on('trans_notifications');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caclculates');
    }
};
