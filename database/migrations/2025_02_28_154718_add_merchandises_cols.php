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
            $table->string('api_key', 255)->nullable()->comment('API 키');
            $table->string('enc_key', 255)->nullable()->comment('암호화 키');
            $table->string('iv', 255)->nullable()->comment('IV');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('api_key');
            $table->dropColumn('enc_key');
            $table->dropColumn('iv');
        });
    }
};
