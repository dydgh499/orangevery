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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcht_id')->comment('가맹점 FK'); 
            $table->foreignId('pg_id')->comment('PG FK'); 
            $table->string('service_type', 100)->comment('서비스 타입(0=예금주 조회, 1=..)');
            $table->string('api_key', 100)->comment('API KEY');
            $table->string('secret_key', 100)->comment('secret KEY');
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
        Schema::dropIfExists('services');
    }
};
