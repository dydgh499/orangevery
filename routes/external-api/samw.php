<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\External\Samw\SamwController;

Route::post('sign-in', [SamwController::class, 'login']);
Route::middleware('auth:sanctum', 'is.external.enable.ip')->group(function() {
    Route::get('withdraws/balance', [SamwController::class, 'withdrawsBalance']);
    Route::post('withdraws', [SamwController::class, 'withdrawsStore']);
});
