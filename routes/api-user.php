<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\Merchandise\TerminalController;
use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\Merchandise\RegularCreditCardController;
use App\Http\Controllers\Manager\Merchandise\NotiUrlController;
use App\Http\Controllers\Manager\Merchandise\SpecifiedTimeDisablePaymentController;
use App\Http\Controllers\Manager\Merchandise\HandHeldTerminalProductController;
use App\Http\Controllers\Manager\Merchandise\BillKeyController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateMchtController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBillKeyController;

use App\Http\Controllers\Manager\SalesforceController;
use App\Http\Controllers\Manager\Salesforce\FeeTableController;
use App\Http\Controllers\Manager\Salesforce\UnderAutoSettingController;
use App\Http\Controllers\Manager\Salesforce\SalesRecommenderCodeController;

use App\Http\Controllers\Log\SubBusinessRegistrationController;
use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Http\Controllers\Log\NotiSendHistoryController;

use App\Http\Controllers\Manager\AuthInfo\AuthInfoController;
use App\Http\Controllers\Manager\Merchandise\ShoppingMall\CategoryController;
use App\Http\Controllers\Manager\Merchandise\ShoppingMall\ProductController;
use App\Http\Controllers\Manager\Merchandise\ShoppingMall\ProductOptionController;
use App\Http\Controllers\Manager\Merchandise\ShoppingMall\ProductOptionGroupController;
use App\Http\Controllers\Manager\Merchandise\ShoppingMall\ShopController;
use App\Http\Controllers\Manager\Gmid\GmidController;

use App\Http\Controllers\Manager\Withdraws\VirtualAccountController;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountHistoryController;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountWithdrawController;

Route::middleware(['auth.update'])->group(function() {
    Route::prefix('salesforces')->group(function() {
        Route::post('{id}/password-change', [SalesforceController::class, 'passwordChange']);
        Route::get('chart', [SalesforceController::class, 'chart']);
        Route::get('fee-apply-histories', [SalesforceController::class, 'feeApplyHistories']);  // 간편보기
        Route::get('classification', [SalesforceController::class, 'classification']);
        //FIXPLUS
        Route::middleware(['is.edit.able'])->group(function() {
            Route::prefix('fee-change-histories')->group(function() {
                Route::delete('batch-remove', [FeeChangeHistoryController::class, 'deleteSalesforceBatch']);
                Route::delete('{id}', [FeeChangeHistoryController::class, 'deleteSalesforce']);
                Route::post('{user}/{type}', [FeeChangeHistoryController::class, 'apply']);
            });
            Route::post('{id}/mcht-batch-fee', [SalesforceController::class, 'mchtBatchFee']);
            Route::post('{id}/2fa-qrcode', [SalesforceController::class, 'create2FAQRLink']);  
            Route::post('{id}/2fa-qrcode/init', [SalesforceController::class, 'init2FA']);  
            Route::post('{id}/2fa-qrcode/create-vertify', [SalesforceController::class, 'vertify2FAQRLink']);
        });
        Route::middleware(['is.operate'])->group(function() {
            Route::middleware(['is.edit.able'])->group(function() {
                Route::post('{id}/unlock-account', [SalesforceController::class, 'unlockAccount']);
            });
            Route::apiResource('under-auto-settings', UnderAutoSettingController::class);    
        });
        Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'salesforce']);
        Route::apiResource('fee-table', FeeTableController::class);
        Route::apiResource('sales-recommender-codes', SalesRecommenderCodeController::class);
    });
    Route::apiResource('salesforces', SalesforceController::class);
        
    Route::prefix('merchandises')->group(function() {
        // 가맹점만 시간영향받지않고 패스워드 초기화 및 unlock 가능
        Route::post('{id}/password-change', [MerchandiseController::class, 'passwordChange']);
        Route::post('{id}/unlock-account', [MerchandiseController::class, 'unlockAccount']);
        Route::middleware(['is.operate'])->get('{id}/shop-code', [ShopController::class, 'shopCode']);
        Route::get('chart', [MerchandiseController::class, 'chart']);
        Route::get('all', [MerchandiseController::class, 'all']);   
        Route::get('terminals', [TerminalController::class, 'index']);   
        Route::middleware(['is.edit.able'])->delete('bill-keys/batch-updaters/remove', [BatchUpdateBillKeyController::class, 'batchRemove']);

        Route::get('pay-modules/chart', [PaymentModuleController::class, 'chart']);
        Route::get('pay-modules/all', [PaymentModuleController::class, 'all']);
        Route::get('noti-send-histories', [NotiSendHistoryController::class, 'index']);
        Route::prefix('noti-send-histories')->group(function() {
            Route::get('{id}', [NotiSendHistoryController::class, 'show']);
            Route::delete('{id}', [NotiSendHistoryController::class, 'destory']);
            Route::post('/retry', [NotiSendHistoryController::class, 'retry']);
            Route::post('self-retry', [NotiSendHistoryController::class, 'selfRetry']);    
        });

        Route::prefix('shopping-mall')->group(function() {
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('products', ProductController::class);
            Route::apiResource('product-options', ProductOptionController::class);
            Route::apiResource('product-option-groups', ProductOptionGroupController::class);
        });

        Route::middleware(['is.edit.able'])->group(function() {
            Route::post('fee-change-histories/{user}/set-fee', [FeeChangeHistoryController::class, 'apply']);
            Route::post('batch-updaters/{user}/set-fee', [BatchUpdateMchtController::class, 'feeApply']);
            Route::post('{id}/set-settle-hold', [MerchandiseController::class, 'setSettleHold']);
        });

        Route::middleware(['is.operate'])->group(function() {
            Route::middleware(['is.edit.able'])->group(function() { 
                Route::post('{id}/clear-settle-hold', [MerchandiseController::class, 'clearSettleHold']);

                Route::apiResource('products', ProductController::class);
                Route::apiResource('handheld-terminal-products', HandHeldTerminalProductController::class);
                Route::delete('fee-change-histories/batch-remove', [FeeChangeHistoryController::class, 'deleteMerchandiseBatch']);
                Route::delete('fee-change-histories/{id}', [FeeChangeHistoryController::class, 'deleteMerchandise']);
            });
            Route::get('sub-business-registrations', [SubBusinessRegistrationController::class, 'index']);
        
            Route::prefix('pay-modules')->group(function() {               
                Route::middleware(['is.edit.able'])->group(function() {
                    Route::post('tid-create', [PaymentModuleController::class, 'tidCreate']);
                    Route::post('mid-create', [PaymentModuleController::class, 'midCreate']);
                    Route::post('mid-bulk-create', [PaymentModuleController::class, 'midBulkCreate']);
                    Route::post('tid-bulk-create', [PaymentModuleController::class, 'tidBulkCreate']);
                    Route::post('pay-key-create', [PaymentModuleController::class, 'payKeyCreate']);
                    Route::post('sign-key-create', [PaymentModuleController::class, 'signKeyCreate']);
                });
                Route::apiResource('bill-keys', BillKeyController::class); 
            });
        });   
        Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'merchandise']);       
        Route::get('bill-keys', [BillKeyController::class, 'managerIndex']); 
        Route::get('noti-urls/chart', [NotiUrlController::class, 'chart']);
        
        Route::apiResource('pay-modules', PaymentModuleController::class);
        Route::apiResource('regular-credit-cards', RegularCreditCardController::class);     
        Route::apiResource('noti-urls', NotiUrlController::class);
        Route::apiResource('specified-time-disable-payments', SpecifiedTimeDisablePaymentController::class);
    });
    Route::apiResource('merchandises', MerchandiseController::class);
    
    Route::prefix('virtual-accounts')->group(function() {
        Route::get('histories/withdraw-statement', [VirtualAccountHistoryController::class, 'withdrawStatement']);
        Route::get('histories/chart', [VirtualAccountHistoryController::class, 'chart']);
        Route::get('histories', [VirtualAccountHistoryController::class, 'index']);
        Route::get('withdraw-histories/chart', [VirtualAccountWithdrawController::class, 'chart']);
        Route::get('withdraw-histories', [VirtualAccountWithdrawController::class, 'index']);
        Route::get('wallets/all', [VirtualAccountController::class, 'all']);
        Route::apiResource('wallets', VirtualAccountController::class);
    });

    Route::prefix('gmids')->group(function() {
        Route::post('{id}/password-change', [GmidController::class, 'passwordChange']);
    });
    Route::apiResource('gmids', GmidController::class); 
});
