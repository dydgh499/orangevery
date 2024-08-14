<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\Service\PaymentGatewayController;
use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Manager\Dashboard\DashboardController;
use App\Http\Controllers\Manager\Service\MchtBlacklistController;
use App\Http\Controllers\Manager\Service\CMSTransactionController;

use App\Http\Controllers\QuickView\QuickViewController;
use App\Http\Controllers\QuickView\PayWindowController;

use App\Http\Controllers\BeforeSystem\BeforeSystemController;

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
    Route::get('services/mcht-blacklists/all', [MchtBlacklistController::class, 'all']);
    Route::get('merchandises/{id}/sale-slip', [MerchandiseController::class, 'saleSlip']);
    Route::get('pay-gateways/{id}/sale-slip', [PaymentGatewayController::class, 'saleSlip']);
    Route::get('pay-modules/{id}/sale-slip', [PaymentModuleController::class, 'saleSlip']);
    Route::get('pay-windows/{window_code}', [PayWindowController::class, 'window']);


    Route::post('transactions/hand-pay', [TransactionController::class, 'handPay']);
    Route::post('transactions/pay-cancel', [TransactionController::class, 'payCancel']);

    Route::prefix('bonaejas')->group(function() {
        Route::post('mobile-code-issuance', [MessageController::class, 'mobileCodeIssuence']);
        Route::post('mobile-code-auth', [MessageController::class, 'mobileCodeAuth']);
        Route::middleware(['auth:sanctum', 'log.route'])->group(function () {
            Route::post('sms-link-send', [MessageController::class, 'smslinkSend']);
            Route::middleware(['is.operate', 'is.edit.able'])->post('mobile-code-head-office-issuence', [MessageController::class, 'headOfficeMobileCodeIssuence']);
        });
    });
    
    Route::prefix('auth')->middleware(['log.route'])->group(function() {
        Route::post('reset-password', [AuthController::class, 'resetPassword']);    
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::post('sign-up', [AuthController::class, 'signUp']);
        Route::post('2fa-qrcode/vertify', [AuthController::class, 'vertify2FA']);  

        Route::middleware(['auth:sanctum'])->group(function() {
            Route::post('extend-password-at', [AuthController::class, 'extendPasswordAt']);   
            Route::post('sign-out', [AuthController::class, 'signout']);
            Route::post('owner-check', [CMSTransactionController::class, 'ownerCheck']);
        });
    }); 

    Route::prefix('manager')->middleware(['auth:sanctum', 'log.route'])->group(function() {
        Route::post('computational-transfer/login', [BeforeSystemController::class, 'login']);
        Route::post('computational-transfer/register', [BeforeSystemController::class, 'register']);
        Route::prefix('dashsboards')->group(function() {
            Route::get('monthly-transactions-analysis', [DashboardController::class, 'monthlyTranAnalysis']);
            Route::get('upside-merchandises-analysis', [DashboardController::class, 'upSideMchtAnalysis']);
            Route::get('upside-salesforces-analysis', [DashboardController::class, 'upSideSaleAnalysis']);
            Route::get('recent-danger-histories', [DashboardController::class, 'getRecentDangerHistories']);
            Route::get('recent-operator-histories', [DashboardController::class, 'getRecentOperatorHistories']);
            Route::get('locked-users', [DashboardController::class, 'getLockedUsers']);            
        });
    });

    Route::prefix('quick-view')->middleware(['auth:sanctum', 'log.route'])->group(function() {
        Route::get('', [QuickViewController::class, 'index']);
        Route::get('withdraw-able-amount', [QuickViewController::class, 'withdrawAbleAmount']);        
        Route::get('pay-modules/{id}/renew', [PayWindowController::class, 'renew']);
    });
});
