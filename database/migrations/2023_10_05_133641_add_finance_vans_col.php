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
        Schema::table('finance_vans', function (Blueprint $table) {
            $table->string('sub_key', 80)->nullable()->comment('sub key');
            $table->string('uid', 80)->nullable()->comment('유저 ID');

            $table->dropColumn('sms_key');
            $table->dropColumn('sms_id');
            $table->dropColumn('sms_sender_phone');
            $table->dropColumn('sms_receive_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_vans', function (Blueprint $table) {
            //
        });
    }
};
