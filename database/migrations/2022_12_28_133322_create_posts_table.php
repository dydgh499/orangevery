<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('level')->default(0)->nullable()->comment('레벨');
            $table->string('title')->default('')->nullable();
            $table->text('content')->nullable();
            $table->integer('type')->default(0)->nullable()->comment('게시글 타입(0=공지사항, 1=FAQ, 2=1:1문의)');
            $table->string('writer')->default('')->comment('게시글 작성자 명');
            $table->integer('parent_id')->nullable();
            $table->boolean('is_reply')->default(false)->comment('답변 여부');
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
        Schema::dropIfExists('posts');
    }
}
