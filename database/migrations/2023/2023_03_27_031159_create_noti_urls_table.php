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
        Schema::create('noti_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('사용 가맹점 ID')->constrained('merchandises')->onDelete('SET NULL');
            $table->string('send_url')->comment('발송 url');
            $table->string('note', 200)->nullable()->comment('비고');
            $table->boolean('noti_status')->default(true)->comment('사용 여부(0=미사용, 1=사용)');
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
        Schema::dropIfExists('noti_urls');
    }
};
