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
        Schema::create('settle_histories_merchandises_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('settle_hist_mcht_id')->nullable()->comment('가맹점 정산 이력 ID');
            $table->foreign('settle_hist_mcht_id', 'shmd_shmcht_id_fk')
                ->references('id')
                ->on('settle_histories_merchandises')
                ->onDelete('SET NULL');

            $table->string('trans_seq_num', 20)->nullable()->index()->comment('요청 ID');
            $table->string('result_code', 5)->nullable()->comment('응답코드');
            $table->string('message', 50)->nullable()->comment('내용');    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settle_histories_merchandises_deposits');
    }
};
