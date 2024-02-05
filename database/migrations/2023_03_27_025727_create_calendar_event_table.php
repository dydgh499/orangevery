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
            $table->increments('id');
            $table->unsignedInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->integer('user_id')->comment('유저 ID');
            $table->tinyInteger('user_type')->default(0)->comment('유저 타입(0=일반유저, 1=가맹점, 2=영업자)');
            $table->string('title', 100)->comment('제목');
            $table->string('location')->comment('위치');
            $table->string('event_url')->comment('event url');
            $table->text('description')->comment('내용');
            $table->date('start_dt')->comment('가맹점명');
            $table->date('end_dt', 100)->comment('가맹점명');
            $table->tinyInteger('is_all_day')->default(0)->comment('하루종일');
            $table->tinyInteger('event_type')->default(0)->comment('이벤트 타입(0=퍼스널,1=비즈니스,2=가족,3=기념일,4=기타)');
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
        Schema::dropIfExists('calendar_events');
    }
};
