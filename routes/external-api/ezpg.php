<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ezpg\EzpgController;

Route::post('sign-in', [EzpgController::class, 'login']);
Route::middleware('auth:sanctum')->group(function() {
    Route::get('transactions', [EzpgController::class, 'transactionIndex']);
    Route::get('reconciliation/summary', [EzpgController::class, 'summary']);
    Route::get('reconciliation', [EzpgController::class, 'reconciliation']);
});
