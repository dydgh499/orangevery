<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\OperatorController;

use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\SalesforceController;
use App\Http\Controllers\Manager\TerminalController;
use App\Http\Controllers\Manager\PaymentModuleController;
use App\Http\Controllers\Manager\PaymentGatewayController;
use App\Http\Controllers\Manager\PaymentSectionController;
use App\Http\Controllers\Manager\NotiUrlController;

use App\Http\Controllers\Manager\ClassificationController;
use App\Http\Controllers\Manager\PostController;
use App\Http\Controllers\Manager\ComplaintController;
use App\Http\Controllers\Manager\TransactionController;
use App\Http\Controllers\Manager\SalesforceBatchController;

use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Http\Controllers\Log\NotiSendHistoryController;
use App\Http\Controllers\Log\OperatorHistoryContoller;
use App\Http\Controllers\Log\SettleHistoryController;
use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Log\FailTransController;

use App\Http\Controllers\QuickView\QuickViewController;
use App\Http\Controllers\BeforeSystem\BeforeSystemController;

use App\Http\Controllers\Manager\Settle\MerchandiseController as MchtSettleController;
use App\Http\Controllers\Manager\Settle\SalesforceController as SalesSettleController;
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

Route::prefix('v1')->middleware('log.route')->group(function() {    
    Route::get('merchandises/{id}/sale-slip', [MerchandiseController::class, 'saleSlip']);
    Route::get('pay-gateways/{id}/sale-slip', [PaymentGatewayController::class, 'saleSlip']);
    
    Route::post('transactions/hand-pay', [TransactionController::class, 'handPay']);
    Route::post('computational-transfer/login', [BeforeSystemController::class, 'login']);
    Route::post('computational-transfer/register', [BeforeSystemController::class, 'register']);

    Route::prefix('auth')->group(function() {
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::post('sign-up', [AuthController::class, 'signUp']);
        Route::middleware('auth:sanctum')->post('sign-out', [AuthController::class, 'signout']);
        Route::middleware('auth:sanctum')->post('ok', [AuthController::class, 'ok']);
        Route::middleware('auth:sanctum')->post('onwer-check', [AuthController::class, 'onwerCheck']);
    });

    Route::prefix('manager')->middleware('auth:sanctum')->group(function() {
        Route::prefix('dashsboards')->group(function() {
            Route::get('monthly-transactions-analysis', [DashboardController::class, 'monthlyTranAnalysis']);
            Route::get('upside-merchandises-analysis', [DashboardController::class, 'upSideMchtAnalysis']);
            Route::get('upside-salesforces-analysis', [DashboardController::class, 'upSideSaleAnalysis']);
            Route::get('recent-danger-histories', [DashboardController::class, 'getRecentDangerHistories']);
            Route::get('recent-operator-histories', [DashboardController::class, 'getRecentOperatorHistories']);
        });

        Route::prefix('posts')->group(function() {
            Route::get('recent', [PostController::class, 'recent']);
            Route::post('upload', [PostController::class, 'upload']);    
        });
        Route::prefix('services')->group(function() {
            Route::get('pay-gateways/detail', [PaymentGatewayController::class, 'detail']);
            Route::post('operators/password-change', [OperatorController::class, 'passwordChange']);

            Route::apiResource('brands', BrandController::class);
            Route::apiResource('operators', OperatorController::class);
            Route::apiResource('operator-histories', OperatorHistoryContoller::class);
            Route::apiResource('pay-gateways', PaymentGatewayController::class);
            Route::apiResource('pay-sections', PaymentSectionController::class);
            Route::apiResource('classifications', ClassificationController::class);
        });
        Route::prefix('transactions')->group(function() {
            Route::post('cancel', [TransactionController::class, 'cancel']);
            Route::post('pay-cancel', [TransactionController::class, 'payCancel']);
            Route::get('chart', [TransactionController::class, 'chart']);

            Route::get('fails', [FailTransController::class, 'index']);
            Route::get('dangers', [DangerTransController::class, 'index']);
            Route::delete('dangers/{id}', [DangerTransController::class, 'destroy']);
            Route::post('dangers/{id}/checked', [DangerTransController::class, 'checked']);
                        
            Route::prefix('settle')->group(function() {
                Route::get('merchandises', [MchtSettleController::class, 'index']);
                Route::get('merchandises/chart', [MchtSettleController::class, 'chart']);
                Route::post('merchandises/deduct', [MchtSettleController::class, 'deduct']);
                Route::post('merchandises/{id}', [MchtSettleController::class, 'part']);
                
                Route::get('salesforces', [SalesSettleController::class, 'index']);
                Route::get('salesforces/chart', [SalesSettleController::class, 'chart']);
                Route::post('salesforces/deduct', [SalesSettleController::class, 'deduct']);
                Route::post('salesforces/{id}', [SalesSettleController::class, 'part']);
            });
            Route::prefix('settle-histories')->group(function() {
                Route::get('merchandises', [SettleHistoryController::class, 'indexMerchandise']);
                Route::post('merchandises', [SettleHistoryController::class, 'createMerchandise']);
                Route::delete('merchandises/{id}', [SettleHistoryController::class, 'deleteMerchandise']);
                Route::post('merchandises/{id}/deposit', [SettleHistoryController::class, 'depositMerchandise']);

                Route::get('salesforces', [SettleHistoryController::class, 'indexSalesforce']);
                Route::post('salesforces', [SettleHistoryController::class, 'createSalesforce']);
                Route::delete('salesforces/{id}', [SettleHistoryController::class, 'deleteSalesforce']);
                Route::post('salesforces/{id}/deposit', [SettleHistoryController::class, 'depositSalesforce']);    
            });
        });
        Route::prefix('salesforces')->group(function() {
            Route::get('chart', [SalesforceController::class, 'chart']);
            Route::get('fee-apply-histories', [SalesforceController::class, 'feeApplyHistories']);
            Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'salesforce']);
            Route::delete('fee-change-histories/{id}', [FeeChangeHistoryController::class, 'deleteSalesforce']);
            Route::get('classification', [SalesforceController::class, 'classification']);
            Route::post('password-change', [SalesforceController::class, 'passwordChange']);
            Route::post('bulk-register', [SalesforceController::class, 'bulkRegister']);
            Route::prefix('batch')->group(function() {
                Route::post('set-fee', [SalesforceBatchController::class, 'setFee']);
                Route::post('set-custom-filter', [SalesforceBatchController::class, 'setCustomFilter']);
                Route::post('set-abnormal-trans-limit', [SalesforceBatchController::class, 'setAbnormalTransLimit']);
                Route::post('set-dupe-pay-validation', [SalesforceBatchController::class, 'setDupPayValidation']);
                Route::post('set-pay-limit', [SalesforceBatchController::class, 'setPayLimit']);
                Route::post('set-pay-disable-time', [SalesforceBatchController::class, 'setForbiddenPayTime']);
                Route::post('set-show-pay-view', [SalesforceBatchController::class, 'setShowPayView']);
                Route::post('set-noti-url', [SalesforceBatchController::class, 'setNotiUrl']);                
            });
        });
        Route::prefix('merchandises')->group(function() {
            Route::get('chart', [MerchandiseController::class, 'chart']);
            Route::get('all', [MerchandiseController::class, 'all']);   
            Route::get('terminals', [TerminalController::class, 'index']);   
            Route::post('password-change', [MerchandiseController::class, 'passwordChange']);
            Route::post('bulk-register', [MerchandiseController::class, 'bulkRegister']);

            Route::get('pay-modules/chart', [PaymentModuleController::class, 'chart']);
            Route::get('pay-modules/all', [PaymentModuleController::class, 'all']);            
            Route::get('pay-modules/{id}/sales-slip', [PaymentModuleController::class, 'salesSlip']);
            Route::post('pay-modules/tid-create', [PaymentModuleController::class, 'tidCreate']);
            Route::post('pay-modules/pay-key-create', [PaymentModuleController::class, 'payKeyCreate']);
            Route::post('pay-modules/bulk-register', [PaymentModuleController::class, 'bulkRegister']);

            Route::delete('fee-change-histories/{id}', [FeeChangeHistoryController::class, 'deleteMerchandise']);
            Route::post('fee-change-histories/{user}/{type}', [FeeChangeHistoryController::class, 'apply']);
            Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'merchandise']);       

            Route::get('noti-send-histories', [NotiSendHistoryController::class, 'index']);
            Route::get('noti-send-histories/{trans_id}', [NotiSendHistoryController::class, 'detail']);
            Route::post('noti-send-histories/{trans_id}/retry', [NotiSendHistoryController::class, 'retry']);

            Route::apiResource('pay-modules', PaymentModuleController::class); 
            Route::apiResource('noti-urls', NotiUrlController::class); 
        });
        Route::apiResource('complaints', ComplaintController::class);
        Route::apiResource('salesforces', SalesforceController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('merchandises', MerchandiseController::class);
        Route::apiResource('posts', PostController::class);
        
    });

    Route::prefix('quick-view')->middleware('auth:sanctum')->group(function() {
        Route::get('', [QuickViewController::class, 'index']);
        Route::post('sms-link-send', [QuickViewController::class, 'smslinkSend']);
    });
});
