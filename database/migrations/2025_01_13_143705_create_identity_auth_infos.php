<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('identity_auth_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('identitiy_auth_type')->comment('본인인증 타입');
            $table->string('corp_code')->nullable()->comment('법인코드');
            $table->string('api_key')->nullable()->comment('API KEY');
            $table->string('sub_key')->nullable()->comment('SUB KEY');
            $table->string('enc_key')->nullable()->comment('암호화키');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_auth_infos');
    }
};
