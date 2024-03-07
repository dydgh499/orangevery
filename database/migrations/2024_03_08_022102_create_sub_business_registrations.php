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
    {   //하위사업자 등록정보
        Schema::create('sub_business_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mcht_id')->nullable()->comment('가맹점 ID')->constrained('merchandises')->onDelete('SET NULL');
            $table->tinyInteger('pg_type')->comment('PG사 타입');
            $table->tinyInteger('registration_type')->default(0)->comment('(0=신규, 1=해지, 2=변경)');
            $table->tinyInteger('registration_result')->default(-1)->comment('등록완료=0, ~에러코드)');
            $table->string('registration_msg', 100)->default('')->comment('결과 메세지');
            $table->string('card_company_code', 3)->nullable()->comment('카드사 코드');
            $table->string('card_company_name', 20)->nullable()->comment('카드사명');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_business_registrations');
    }
};
