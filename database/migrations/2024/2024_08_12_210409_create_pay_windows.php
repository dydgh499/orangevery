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
        Schema::create('pay_windows', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pmod_id')->nullable()->comment('결제모듈 ID')->constrained('payment_modules')->onDelete('SET NULL');
            $table->string('pin_code', 10)->nullable()->comment('가맹점으로부터 발급받은 핀 코드');
            $table->string('window_code', 10)->index()->nullable()->comment('결제창 코드');
            $table->timestamp('holding_able_at')->nullable()->comment('유지가능시간');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_windows');
    }
};
