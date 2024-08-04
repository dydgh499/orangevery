<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\Merchandise\TerminalController;
use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\Merchandise\RegularCreditCardController;
use App\Http\Controllers\Manager\Merchandise\NotiUrlController;
use App\Http\Controllers\Manager\Merchandise\SpecifiedTimeDisablePaymentController;

use App\Http\Controllers\Manager\SalesforceController;
use App\Http\Controllers\Manager\Salesforce\UnderAutoSettingController;


use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateMchtController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateSalesController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdatePayModuleController;

use App\Http\Controllers\Log\SubBusinessRegistration\SubBusinessRegistrationController;
use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Http\Controllers\Log\NotiSendHistoryController;

Route::prefix('salesforces')->group(function() {
    Route::post('{id}/password-change', [SalesforceController::class, 'passwordChange']);
    Route::get('chart', [SalesforceController::class, 'chart']);
    Route::get('fee-apply-histories', [SalesforceController::class, 'feeApplyHistories']);  // 간편보기
    Route::get('classification', [SalesforceController::class, 'classification']);

        //FIXPLUS
        Route::middleware(['is.edit.able'])->group(function() {
            Route::prefix('fee-change-histories')->group(function() {
                Route::delete('{id}', [FeeChangeHistoryController::class, 'deleteSalesforce']);
                Route::post('{user}/{type}', [FeeChangeHistoryController::class, 'apply']);
            });
            Route::post('{id}/mcht-batch-fee', [SalesforceController::class, 'mchtBatchFee']);
            Route::post('{id}/2fa-qrcode', [SalesforceController::class, 'create2FAQRLink']);  
            Route::post('{id}/2fa-qrcode/create-vertify', [SalesforceController::class, 'vertify2FAQRLink']);
        });
        Route::middleware(['is.operate'])->group(function() {
            Route::prefix('batch-updaters')->middleware(['is.edit.able'])->group(function() {
                Route::post('set-settle-tax-type', [BatchUpdateSalesController::class, 'setSettleTaxType']);
                Route::post('set-settle-cycle', [BatchUpdateSalesController::class, 'setSettleCycle']);
                Route::post('set-settle-day', [BatchUpdateSalesController::class, 'setSettleDay']);
                Route::post('set-is-able-modify-mcht', [BatchUpdateSalesController::class, 'setIsAbleModifyMcht']);
                Route::post('set-view-type', [BatchUpdateSalesController::class, 'setViewType']);
                Route::post('set-account-info', [BatchUpdateSalesController::class, 'setAccountInfo']);
                Route::post('set-note', [BatchUpdateSalesController::class, 'setNote']);
                Route::delete('remove', [BatchUpdateSalesController::class, 'batchRemove']);   
            });
            Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'salesforce']);
            Route::middleware(['is.edit.able'])->group(function() {
                Route::post('{id}/unlock-account', [SalesforceController::class, 'unlockAccount']);
                Route::post('bulk-register', [SalesforceController::class, 'bulkRegister']);
            });
        Route::apiResource('under-auto-settings', UnderAutoSettingController::class);    
    });
});
Route::apiResource('salesforces', SalesforceController::class);
    
Route::prefix('merchandises')->group(function() {
    Route::post('{id}/password-change', [MerchandiseController::class, 'passwordChange']);
    Route::post('{id}/unlock-account', [MerchandiseController::class, 'unlockAccount']);
    Route::get('chart', [MerchandiseController::class, 'chart']);
    Route::get('all', [MerchandiseController::class, 'all']);   
    Route::get('terminals', [TerminalController::class, 'index']);   

    Route::get('pay-modules/chart', [PaymentModuleController::class, 'chart']);
    Route::get('pay-modules/all', [PaymentModuleController::class, 'all']);
    
    Route::get('noti-send-histories', [NotiSendHistoryController::class, 'index']);
    Route::prefix('noti-send-histories')->group(function() {
        Route::get('{trans_id}', [NotiSendHistoryController::class, 'show']);
        Route::post('{trans_id}/retry', [NotiSendHistoryController::class, 'retry']);
        Route::post('batch-retry', [NotiSendHistoryController::class, 'batchRetry']);    
    });

    //FIXPLUS
    Route::middleware(['is.edit.able'])->group(function() {
        Route::post('fee-change-histories/{user}/{type}', [FeeChangeHistoryController::class, 'apply']);
        Route::prefix('batch-updaters')->group(function() {
            Route::post('mcht-fee-direct-apply', [BatchUpdateMchtController::class, 'setMchtFeeDirect']);
            Route::post('mcht-fee-book-apply', [BatchUpdateMchtController::class, 'setMchtFeeBooking']);    
        });
    });

    Route::middleware(['is.operate'])->group(function() {
        Route::middleware(['is.edit.able'])->group(function() {
            Route::prefix('batch-updaters')->group(function() {
                Route::post('sales-fee-direct-apply', [BatchUpdateMchtController::class, 'setSalesFeeDirect']);
                Route::post('sales-fee-book-apply', [BatchUpdateMchtController::class, 'setSalesFeeBooking']);
                Route::post('set-noti-url', [BatchUpdateMchtController::class, 'setNotiUrl']);
                Route::post('set-enabled', [BatchUpdateMchtController::class, 'setEnabled']);
                Route::post('set-custom-filter', [BatchUpdateMchtController::class, 'setCustomFilter']);
                Route::post('set-business-num', [BatchUpdateMchtController::class, 'setBusinessNum']);
                Route::post('set-account-info', [BatchUpdateMchtController::class, 'setAccountInfo']);
                Route::post('set-show-fee', [BatchUpdateMchtController::class, 'setShowFee']);
                Route::delete('remove', [BatchUpdateMchtController::class, 'batchRemove']);
            });    
            Route::post('{id}/set-settle-hold', [MerchandiseController::class, 'setSettleHold']);
            Route::post('{id}/clear-settle-hold', [MerchandiseController::class, 'clearSettleHold']);
            Route::post('bulk-register', [MerchandiseController::class, 'bulkRegister']);    
            Route::post('regular-credit-cards/bulk-register', [RegularCreditCardController::class, 'bulkRegister']);
            Route::post('noti-urls/bulk-register', [NotiUrlController::class, 'bulkRegister']);
    
            Route::apiResource('specified-time-disable-payments', SpecifiedTimeDisablePaymentController::class);
            Route::delete('fee-change-histories/{id}', [FeeChangeHistoryController::class, 'deleteMerchandise']);
        });
        Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'merchandise']);       
        Route::get('sub-business-registrations', [SubBusinessRegistrationController::class, 'index']);
    
        Route::prefix('pay-modules')->group(function() {
            Route::middleware(['is.edit.able'])->group(function() {
                Route::prefix('batch-updaters')->group(function() {
                    Route::post('set-payment-gateway', [BatchUpdatePayModuleController::class, 'setPaymentGateway']);
                    Route::post('set-abnormal-trans-limit', [BatchUpdatePayModuleController::class, 'setAbnormalTransLimit']);
                    Route::post('set-dupe-pay-count-validation', [BatchUpdatePayModuleController::class, 'setDupPayCountValidation']);
                    Route::post('set-dupe-pay-least-validation', [BatchUpdatePayModuleController::class, 'setDupPayLeastValidation']);
                    Route::post('set-mid', [BatchUpdatePayModuleController::class, 'setMid']);
                    Route::post('set-tid', [BatchUpdatePayModuleController::class, 'setTid']);
                    Route::post('set-api-key', [BatchUpdatePayModuleController::class, 'setApiKey']);
                    Route::post('set-sub-key', [BatchUpdatePayModuleController::class, 'setSubKey']);
                    Route::post('set-installment', [BatchUpdatePayModuleController::class, 'setInstallment']);
                    Route::post('set-note', [BatchUpdatePayModuleController::class, 'setNote']);
                    Route::post('set-pay-limit', [BatchUpdatePayModuleController::class, 'setPayLimit']);
                    Route::post('set-pay-disable-time', [BatchUpdatePayModuleController::class, 'setForbiddenPayTime']);
                    Route::post('set-show-pay-view', [BatchUpdatePayModuleController::class, 'setShowPayView']);
                    Route::post('set-use-realtime-deposit', [BatchUpdatePayModuleController::class, 'setUseRealtimeDeposit']);
                    Route::post('set-fin-id', [BatchUpdatePayModuleController::class, 'setFinId']);
                    Route::post('set-payment-term-min', [BatchUpdatePayModuleController::class, 'setPaymentTermMin']);
                    Route::delete('remove', [BatchUpdatePayModuleController::class, 'batchRemove']);   
                });
                Route::post('tid-create', [PaymentModuleController::class, 'tidCreate']);
                Route::post('mid-create', [PaymentModuleController::class, 'midCreate']);
                Route::post('mid-bulk-create', [PaymentModuleController::class, 'midBulkCreate']);
                Route::post('tid-bulk-create', [PaymentModuleController::class, 'tidBulkCreate']);
                Route::post('pay-key-create', [PaymentModuleController::class, 'payKeyCreate']);
                Route::post('sign-key-create', [PaymentModuleController::class, 'signKeyCreate']);
                Route::post('bulk-register', [PaymentModuleController::class, 'bulkRegister']);
                Route::post('pg-bulk-updater', [PaymentModuleController::class, 'bulkRegisterPG']);
            });
        });
    });
    
    Route::apiResource('pay-modules', PaymentModuleController::class);
    Route::apiResource('regular-credit-cards', RegularCreditCardController::class);     
    Route::apiResource('noti-urls', NotiUrlController::class);
});
Route::apiResource('merchandises', MerchandiseController::class);
