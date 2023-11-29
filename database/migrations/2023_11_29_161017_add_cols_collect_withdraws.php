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
        Schema::table('collect_withdraws', function (Blueprint $table) {
            $table->string('trans_seq_num', 20)->nullable()->index()->comment('요청 ID');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->string('message', 100)->nullable()->comment('내용');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collect_withdraws', function (Blueprint $table) {
            //
        });
    }
};
