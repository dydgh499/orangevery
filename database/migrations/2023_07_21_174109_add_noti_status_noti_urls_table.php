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
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->boolean('noti_status')->default(true)->comment('사용 여부(0=미사용, 1=사용)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('noti_urls', function (Blueprint $table) {
            $table->dropColumn('noti_status');
        });
    }
};
