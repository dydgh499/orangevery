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
        Schema::create('payment_module_column_apply_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('pmod_id')->nullable()->comment('결제모듈 ID')->constrained('payment_modules')->onDelete('SET NULL');
            $table->string('book_column', 150)->nullable()->comment('예약적용 할 대상');
            $table->string('book_value')->index()->nullable()->comment('예약적용 한 값');
            $table->timestamp('apply_at')->index()->nullable()->comment('예약시간');
            $table->boolean('change_status')->default(false)->comment('변경타입(0=적용대기, 1=변경완료)');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_module_column_apply_books');
    }
};
