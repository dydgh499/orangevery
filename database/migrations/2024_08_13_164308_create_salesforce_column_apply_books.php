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
        Schema::create('salesforce_column_apply_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->unsignedInteger('sales_id')->nullable()->comment('영업점 ID')->constrained('salesforces')->onDelete('SET NULL');
            $table->string('apply_data', 500)->nullable()->comment('적용 할 값');
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
        Schema::dropIfExists('salesforce_column_apply_books');
    }
};
