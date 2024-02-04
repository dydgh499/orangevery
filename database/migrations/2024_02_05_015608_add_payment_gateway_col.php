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
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('rep_mid', 30)->nullable()->comment('대표 가맹점 MID');
            $table->string('sftp_id', 30)->nullable()->comment('SFTP 접속 id');
            $table->string('sftp_password', 30)->nullable()->comment('SFTP 접속 pw');
            $table->boolean('use_different_settlement')->default(false)->comment('차액정산 사용여부');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            //
        });
    }
};
