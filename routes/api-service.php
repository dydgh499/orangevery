<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\Service\ExceptionWorkTimeController;
use App\Http\Controllers\Manager\Service\AbnormalConnectionController;

use App\Http\Controllers\Manager\Service\CMSTransactionController;
use App\Http\Controllers\Manager\Service\BankAccountController;

use App\Http\Controllers\Manager\Service\OperatorIPController;

use App\Http\Controllers\Manager\Service\FinanceVanController;
use App\Http\Controllers\Manager\Service\PaymentGatewayController;
use App\Http\Controllers\Manager\Service\PaymentSectionController;
use App\Http\Controllers\Manager\Service\CMSTransactionBookController;

use App\Http\Controllers\Manager\OperatorController;

use App\Http\Controllers\Log\ActivityHistoryContoller;

use App\Http\Controllers\V1\V1WithdrawBookController;

Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
    Route::get('services/pay-gateways/detail', [PaymentGatewayController::class, 'detail']);
    Route::get('services/activity-histories/{target_id}/target', [ActivityHistoryContoller::class, 'target']); 

    Route::prefix('services')->group(function() {
        Route::get('bonaejas', [MessageController::class, 'index']);
        Route::get('bonaejas/chart', [MessageController::class, 'chart']);
        Route::get('abnormal-connection-histories/secure-report', [AbnormalConnectionController::class, 'secureReport']);
        Route::get('abnormal-connection-histories/secure-report/detail-work-status', [AbnormalConnectionController::class, 'detailWorkStatus']);
        Route::get('abnormal-connection-histories/last-login', [AbnormalConnectionController::class, 'findLastLogin']);
        Route::get('abnormal-connection-histories', [AbnormalConnectionController::class, 'index']);

        Route::middleware(['is.edit.able'])->group(function() {
            Route::post('operators/{id}/password-change', [OperatorController::class, 'passwordChange']);
            Route::post('operators/{id}/unlock-account', [OperatorController::class, 'unlockAccount']);  
            Route::post('operators/{id}/2fa-qrcode', [OperatorController::class, 'create2FAQRLink']);  
            Route::post('operators/{id}/2fa-qrcode/init', [OperatorController::class, 'init2FA']);  
            Route::post('operators/{id}/2fa-qrcode/create-vertify', [OperatorController::class, 'vertify2FAQRLink']);
            Route::post('cms-transactions/withdraw', [CMSTransactionController::class, 'withdraw']);
        });
    
        
        Route::apiResource('operator-ips', OperatorIPController::class);
        Route::apiResource('brands', BrandController::class);
    
        Route::apiResource('operators', OperatorController::class);            
        Route::get('activity-histories', [ActivityHistoryContoller::class, 'index']);
        Route::get('activity-histories/{id}/detail', [ActivityHistoryContoller::class, 'detail']); 
        
        Route::apiResource('pay-gateways', PaymentGatewayController::class);
        Route::apiResource('pay-sections', PaymentSectionController::class);
        Route::apiResource('finance-vans', FinanceVanController::class);
        Route::apiResource('exception-work-times', ExceptionWorkTimeController::class);        
        Route::get('cms-transactions', [CMSTransactionController::class, 'index']);
        Route::get('cms-transactions/chart', [CMSTransactionController::class, 'chart']);
        Route::post('cms-transactions/get-balance', [CMSTransactionController::class, 'getBalance']);
    });

    Route::prefix('virtuals')->group(function() {
        Route::get('bank-accounts', [BankAccountController::class, 'index']);
        Route::delete('bank-accounts/{id}', [BankAccountController::class, 'destroy']);
        Route::get('cms-transaction-books', [CMSTransactionBookController::class, 'index']);
        Route::post('cms-transaction-books/cancel-job', [V1WithdrawBookController::class, 'cancelJob']);

        Route::get('cms-transactions', [CMSTransactionController::class, 'index']);
        Route::get('cms-transactions/chart', [CMSTransactionController::class, 'chart']);
        Route::post('cms-transactions/get-balance', [CMSTransactionController::class, 'getBalance']);
    });
});
