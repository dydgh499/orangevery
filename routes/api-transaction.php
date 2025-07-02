<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\Transaction\TransactionController;

Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
    Route::prefix('transactions')->group(function() {
        Route::post('cancel', [TransactionController::class, 'cancel']);
        Route::get('chart', [TransactionController::class, 'chart']);         
    });
    Route::apiResource('transactions', TransactionController::class);
});
