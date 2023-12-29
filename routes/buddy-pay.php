<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuddyPay\BuddyPayController;

Route::middleware('log.route')->group(function() {
    Route::post('sign-in', [BuddyPayController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('pay-modules', [BuddyPayController::class, 'payModules']);
        Route::get('transactions', [BuddyPayController::class, 'transactionIndex']);
        Route::post('pay/hand', [BuddyPayController::class, 'handPay']);
    });
});
