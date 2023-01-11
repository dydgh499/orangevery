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
        Schema::create('trans_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcht_id')->comment('가맹점 FK');
            $table->string('send_url')->comment('노티 URL');
            $table->integer('retry_interval')->default(10)->comment('재전송 간격(min)');
            $table->integer('retry_count')->default(5)->comment('재전송 회수');
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
        Schema::dropIfExists('trans_notifications');
    }
};
