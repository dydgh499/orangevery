<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Manager\Service\CMSTransactionController;

use App\Http\Controllers\QuickView\PayWindowController;
use App\Http\Controllers\Manager\Merchandise\BillKeyController;


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
    Route::get('pay/sales-slip/{ord_num}', [PayWindowController::class, 'salesSlip']);
    Route::prefix('pay')->group(function() {
        Route::middleware(['log.route'])->get('test', [PayWindowController::class, 'testWindow']);
        Route::prefix('{window_code}')->group(function() {
            Route::post('auth', [PayWindowController::class, 'auth']);
            Route::post('bill-keys/{id}/pay', [BillKeyController::class, 'pay']);
            Route::apiResource('bill-keys', BillKeyController::class);
        });
        Route::apiResource('bill-keys', BillKeyController::class);
    });
    Route::post('transactions/hand-pay', [TransactionController::class, 'handPay']);
    Route::post('transactions/pay-cancel', [TransactionController::class, 'payCancel']);

    Route::prefix('bonaejas')->group(function() {
        Route::post('mobile-code-issuance', [MessageController::class, 'mobileCodeIssuence']);
        Route::post('mobile-code-auth', [MessageController::class, 'mobileCodeAuth']);
        Route::middleware(['auth:sanctum', 'log.route', 'auth.update'])->group(function () {
            Route::post('sms-link-send', [MessageController::class, 'smslinkSend']);
            Route::middleware(['is.operate', 'is.edit.able'])->post('mobile-code-head-office-issuence', [MessageController::class, 'headOfficeMobileCodeIssuence']);
            Route::middleware(['is.operate', 'is.edit.able'])->post('pay-verfication-init', [MessageController::class, 'payVerficationInit']);
        });
    });
    
    Route::prefix('auth')->middleware(['log.route'])->group(function() {
        Route::post('reset-password', [AuthController::class, 'resetPassword']);    
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::post('sign-up', [AuthController::class, 'signUp']);
        Route::post('2fa-qrcode/vertify', [AuthController::class, 'vertify2FA']);  

        Route::middleware(['auth:sanctum'])->group(function() {
            Route::post('sign-out', [AuthController::class, 'signout']);
            Route::post('owner-check', [CMSTransactionController::class, 'ownerCheck']);
        });
    }); 
});
