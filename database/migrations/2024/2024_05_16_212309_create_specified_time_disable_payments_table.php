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
        Schema::create('specified_time_disable_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('mcht_id')->nullable()->comment('가맹점 FK')->constrained('merchandises')->onDelete('SET NULL');
            $table->time('disable_s_tm')->nullable()->comment('결제 금지 시작 시간');
            $table->time('disable_e_tm')->nullable()->comment('결제 금지 종료 시간');
            $table->tinyInteger('disable_type')->default(0)->comment('취소타입(0결제금지, 1=이체금지)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specified_time_disable_payments');
    }
};
