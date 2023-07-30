<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 2023_07_12_180136_update_is_transfer_column_type
        Schema::table('brands', function (Blueprint $table) {
            $table->tinyInteger('is_transfer')->change();
        });
        
        Schema::create('sf_fee_apply_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->comment('브랜드 FK')->constrained('brands')->onDelete('SET NULL');
            $table->foreignId('sales_id')->nullable()->comment('영업점 FK')->constrained('salesforces')->onDelete('SET NULL');
            $table->float('trx_fee',8,5)->nullable()->comment('적용 거래 수수료');
            $table->boolean('is_delete')->default(false)->comment('삭제 여부');
            $table->timestamps();
        });        
        // 230721_add_noti_status_noti_ruls_table
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->boolean('noti_status')->default(true)->comment('사용 여부(0=미사용, 1=사용)');
        });
        
        // 230725_add_pay_key_payment_modules
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->string('pay_key', 100)->default('')->comment('API KEY');
        });
        
        // 230726 payment_modules_filter_issuers_table
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->string('filter_issuers', 200)->default('[]')->comment('카드사 필터');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('is_transfer')->change();
        });

        Schema::dropIfExists('sf_fee_apply_histories');
        
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->dropColumn('noti_status');
        });
        
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('pay_key');
        });
        
        Schema::table('payment_modules', function (Blueprint $table) {
            $table->dropColumn('filter_issuers');
        });
    }
};
