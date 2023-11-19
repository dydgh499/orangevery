<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bf\BfController;

Route::middleware('log.route')->group(function() {
    Route::post('sign-in', [BfController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('pay-modules', [BfController::class, 'payModules']);
        Route::get('withdraws/balance', [BfController::class, 'withdrawsBalance']);
        Route::post('withdraws', [BfController::class, 'withdrawsStore']);
        Route::post('pay/hand', [BfController::class, 'handPay']);
    });
});
