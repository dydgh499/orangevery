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
        Schema::create('cancel_deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('trans_id')->nullable()->comment('취소 FK')->constrained('transactions')->onDelete('SET NULL');
            $table->integer('deposit_amount')->default(0)->comment('입금 금액');
            $table->date('deposit_dt')->nullable()->comment('입금 날짜');
            $table->string('deposit_history', 255)->default('')->comment('입금 내역');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancel_deposits');
    }
};
