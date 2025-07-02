<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Manager\Service\CMSTransactionController;

use App\Http\Controllers\Manager\Pay\BillKeyController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function() {    
    Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
        Route::prefix('pay')->group(function() {
            Route::post('bill-keys/{id}/pay', [BillKeyController::class, 'pay']);
            Route::apiResource('bill-keys', BillKeyController::class);
        });
        Route::post('transactions/hand-pay', [TransactionController::class, 'handPay']);
        Route::post('transactions/pay-cancel', [TransactionController::class, 'payCancel']);

        Route::prefix('bonaejas')->group(function() {
            Route::post('mobile-code-issuance', [MessageController::class, 'mobileCodeIssuence']);
            Route::post('mobile-code-auth', [MessageController::class, 'mobileCodeAuth']);
        });
        
    });

    Route::prefix('auth')->middleware(['log.route'])->group(function() {
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::post('sign-up', [AuthController::class, 'signUp']);
        Route::post('2fa-qrcode/vertify', [AuthController::class, 'vertify2FA']);  

        Route::middleware(['auth:sanctum'])->group(function() {
            Route::post('sign-out', [AuthController::class, 'signout']);
            Route::post('owner-check', [CMSTransactionController::class, 'ownerCheck']);
        });
    }); 
});
