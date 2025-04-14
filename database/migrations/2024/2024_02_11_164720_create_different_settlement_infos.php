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
        Schema::create('different_settlement_infos', function (Blueprint $table) {
            $table->unsignedTinyInteger('id', true);
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->tinyInteger('pg_type')->comment('PG사 타입');
            $table->string('rep_mid', 30)->nullable()->comment('대표 가맹점 MID');
            $table->string('sftp_id', 30)->nullable()->comment('SFTP 접속 id');
            $table->string('sftp_password', 30)->nullable()->comment('SFTP 접속 pw');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('different_settlement_infos');
    }
};
