<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\Service\BeforeBrandInfoController;
use App\Http\Controllers\Manager\Service\ExceptionWorkTimeController;
use App\Http\Controllers\Manager\Service\AbnormalConnectionController;
use App\Http\Controllers\Manager\Service\DifferentSettlementInfoController;
use App\Http\Controllers\Manager\Service\IdentityAuthInfoController;

use App\Http\Controllers\Manager\Service\MchtBlacklistController;
use App\Http\Controllers\Manager\Service\HeadOfficeAccountController;
use App\Http\Controllers\Manager\Service\CMSTransactionController;

use App\Http\Controllers\Manager\Service\ClassificationController;
use App\Http\Controllers\Manager\Service\OperatorIPController;
use App\Http\Controllers\Manager\Service\HolidayController;

use App\Http\Controllers\Manager\Service\FinanceVanController;
use App\Http\Controllers\Manager\Service\PaymentGatewayController;
use App\Http\Controllers\Manager\Service\PaymentSectionController;
use App\Http\Controllers\Manager\Service\PopupController;
use App\Http\Controllers\Manager\Service\CMSTransactionBookController;

use App\Http\Controllers\Manager\OperatorController;

use App\Http\Controllers\Manager\PostController;
use App\Http\Controllers\Manager\ComplaintController;
use App\Http\Controllers\Log\ActivityHistoryContoller;
use App\Http\Controllers\Manager\BatchUpdater\ApplyBookController;

use App\Http\Controllers\Manager\Withdraws\VirtualAccountController;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountHistoryController;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountWithdrawController;

Route::middleware(['auth.update'])->group(function() {
    Route::get('services/pay-gateways/detail', [PaymentGatewayController::class, 'detail']);
    Route::get('services/activity-histories/{target_id}/target', [ActivityHistoryContoller::class, 'target']); 
    Route::get('popups/currently', [PopupController::class, 'currently']);
    Route::get('posts/{id}/parent', [PostController::class, 'parent']);
    Route::get('posts/recent', [PostController::class, 'recent']);

    Route::middleware(['is.operate', 'last.login.ip'])->group(function() {
        Route::middleware(['is.edit.able'])->post('posts/upload', [PostController::class, 'upload']);  
        Route::prefix('services')->group(function() {
            Route::get('bonaejas', [MessageController::class, 'index']);
            Route::get('bonaejas/chart', [MessageController::class, 'chart']);
            Route::get('brands/chart', [BrandController::class, 'chart']);
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
                Route::post('bulk-cms-transactions/bulk-withdraw', [CMSTransactionController::class, 'bulkWithdraw']);
            });
        
            Route::apiResource('brands/before-brand-infos', BeforeBrandInfoController::class);
            Route::apiResource('brands/different-settlement-infos', DifferentSettlementInfoController::class);        
            Route::apiResource('brands/identity-auth-infos', IdentityAuthInfoController::class);        
            
            Route::apiResource('operator-ips', OperatorIPController::class);
            Route::apiResource('brands', BrandController::class);
        
            Route::apiResource('operators', OperatorController::class);            
            Route::get('activity-histories', [ActivityHistoryContoller::class, 'index']);
            Route::get('activity-histories/{id}/detail', [ActivityHistoryContoller::class, 'detail']); 
            
            Route::apiResource('pay-gateways', PaymentGatewayController::class);
            Route::apiResource('pay-sections', PaymentSectionController::class);
            Route::apiResource('finance-vans', FinanceVanController::class);
            Route::apiResource('classifications', ClassificationController::class);
            Route::apiResource('mcht-blacklists', MchtBlacklistController::class);            
            Route::apiResource('holidays', HolidayController::class);
            Route::apiResource('exception-work-times', ExceptionWorkTimeController::class);        
            Route::get('head-office-accounts', [HeadOfficeAccountController::class, 'index']);        
            Route::get('cms-transaction-books', [CMSTransactionBookController::class, 'index']);
            Route::delete('cms-transaction-books/{id}', [CMSTransactionBookController::class, 'destroy']);
            Route::post('cms-transaction-books/cancel-job-test', [CMSTransactionBookController::class, 'cancelJobTest']);
            Route::get('cms-transactions', [CMSTransactionController::class, 'index']);
            Route::get('cms-transactions/chart', [CMSTransactionController::class, 'chart']);
            Route::post('cms-transactions/get-balance', [CMSTransactionController::class, 'getBalance']);
            Route::get('book-applies', [ApplyBookController::class, 'index']);
            Route::delete('book-applies/{dest_type}/{id}', [ApplyBookController::class, 'destroy']);
        });

        Route::post('virtual-accounts/histories/cancel-job', [VirtualAccountHistoryController::class, 'cancelJob']);
        Route::post('virtual-accounts/histories/retry-withdraw', [VirtualAccountHistoryController::class, 'retryWithdraw']);
        Route::post('virtual-accounts/histories/retry-settlement', [VirtualAccountHistoryController::class, 'retrySettlement']);
        Route::apiResource('popups', PopupController::class);
    });

    Route::apiResource('complaints', ComplaintController::class);
    Route::apiResource('posts', PostController::class);
});
