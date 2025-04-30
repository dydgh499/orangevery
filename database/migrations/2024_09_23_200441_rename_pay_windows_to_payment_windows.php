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
        Schema::rename('pay_windows', 'payment_windows');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('payment_windows', 'pay_windows');
    }
};
