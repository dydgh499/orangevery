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
        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('is_lock')->default(0)->comment('계정잠금 여부');
            $table->timestamp('locked_at')->nullable()->comment('계정잠금 시간');
            $table->timestamp('password_change_at')->nullable()->comment('마지막 패스워드 변경시간');
        });
        Schema::table('salesforces', function (Blueprint $table) {
            $table->boolean('is_lock')->default(0)->comment('계정잠금 여부');
            $table->timestamp('locked_at')->nullable()->comment('계정잠금 시간');
            $table->timestamp('password_change_at')->nullable()->comment('마지막 패스워드 변경시간');
        });
        Schema::table('operators', function (Blueprint $table) {
            $table->boolean('is_lock')->default(0)->comment('계정잠금 여부');
            $table->timestamp('locked_at')->nullable()->comment('계정잠금 시간');
            $table->timestamp('password_change_at')->nullable()->comment('마지막 패스워드 변경시간');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
