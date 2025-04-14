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
        Schema::create('shopping_malls', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mcht_id')->index()->nullable()->comment('가맹점');
            $table->string('window_code', 10)->index()->nullable()->comment('쇼핑몰 코드');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_malls');
    }
};
