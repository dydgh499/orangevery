<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bf\BfController;
use App\Http\Controllers\Manager\Merchandise\RegularCreditCardController;

Route::post('sign-in', [BfController::class, 'login']);
Route::middleware('auth:sanctum')->group(function() {
    Route::get('pay-modules', [BfController::class, 'payModules']);
    Route::get('transactions', [BfController::class, 'transactionIndex']);
    Route::get('realtime-histories', [BfController::class, 'realtimeHistoryIndex']);
    Route::get('self-withdraws', [BfController::class, 'selfWithdrawIndex']);        
    Route::get('withdraws/balance', [BfController::class, 'withdrawsBalance']);
    Route::post('withdraws', [BfController::class, 'withdrawsStore']);
    Route::post('pay/hand', [BfController::class, 'handPay']);
    Route::get('occuerred-sale', [BfController::class, 'occuerredSale']);
    Route::apiResource('regular-credit-cards', RegularCreditCardController::class);
});
