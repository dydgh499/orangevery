<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Manager\Transaction\TransactionSummaryController;

use App\Http\Controllers\Log\DifferenceSettlementHistoryController;
use App\Http\Controllers\Log\RealtimeSendHistoryController;
use App\Http\Controllers\Log\MchtSettleHistoryController;
use App\Http\Controllers\Log\SalesSettleHistoryController;
use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Log\FailTransController;

use App\Http\Controllers\Manager\Settle\MerchandiseController as MchtSettleController;
use App\Http\Controllers\Manager\Settle\SalesforceController as SalesSettleController;
use App\Http\Controllers\Manager\Settle\CollectWithdrawController;
use App\Http\Controllers\Manager\Settle\RepMerchandiseController;
use App\Http\Controllers\Manager\Settle\CancelDepositController;

use App\Http\Controllers\Log\CollectWithdrawHistoryController;

Route::prefix('transactions')->group(function() {          
    Route::middleware(['is.operate'])->group(function() {
        Route::middleware(['is.edit.able'])->group(function() {
            Route::post('batch-retry', [TransactionController::class, 'batchRetry']);
            Route::post('batch-self-retry', [TransactionController::class, 'batchSelfRetry']);    
            Route::post('change-settle-date', [TransactionController::class, 'changeSettleDate']);
            Route::post('settle/merchandises/representative-settle', [RepMerchandiseController::class, 'settlement']);
        });
    
        Route::prefix('settle-histories')->group(function() {
            Route::get('difference', [DifferenceSettlementHistoryController::class, 'index']);
            Route::get('difference/chart', [DifferenceSettlementHistoryController::class, 'chart']);
            Route::apiResource('collect-withdraws', CollectWithdrawHistoryController::class);    
            Route::middleware(['is.edit.able'])->group(function() {
                Route::post('merchandises/batch', [MchtSettleHistoryController::class, 'batch']);
                Route::post('merchandises/batch-link-account', [MchtSettleHistoryController::class, 'batchLinkAccount']);
                Route::post('merchandises/{id}/add-deduct', [MchtSettleHistoryController::class, 'addDeduct']);
                Route::post('merchandises/{id}/link-account', [MchtSettleHistoryController::class, 'linkAccount']);
                Route::post('merchandises/{id}/deposit', [MchtSettleHistoryController::class, 'setDeposit']);
                Route::post('merchandises/batch-deposit', [MchtSettleHistoryController::class, 'setBatchDeposit']);                        
    
                Route::post('salesforces/batch', [SalesSettleHistoryController::class, 'batch']);
                Route::post('salesforces/batch-link-account', [SalesSettleHistoryController::class, 'batchLinkAccount']);
                Route::post('salesforces/{id}/add-deduct', [SalesSettleHistoryController::class, 'addDeduct']);
                Route::post('salesforces/{id}/link-account', [SalesSettleHistoryController::class, 'linkAccount']);
                Route::post('salesforces/{id}/deposit', [SalesSettleHistoryController::class, 'setDeposit']);
                Route::post('salesforces/batch-deposit', [SalesSettleHistoryController::class, 'setBatchDeposit']);
            });
            Route::post('merchandises/single-deposit', [MchtSettleHistoryController::class, 'singleDeposit']);
            Route::post('merchandises/single-deposit-cancel-job-reservation', [MchtSettleHistoryController::class, 'singleDepositCancelJobReservation']);
        });
        Route::post('settle/merchandises/deposit-validate', [MchtSettleController::class, 'depositValidate']);
    });

    Route::post('noti/{id}', [TransactionController::class, 'noti']);
    Route::post('cancel', [TransactionController::class, 'cancel']);
    Route::get('chart', [TransactionController::class, 'chart']);
    Route::get('merchandises/groups', [TransactionController::class, 'mchtGroups']);            
    Route::get('fails', [FailTransController::class, 'index']);
    Route::get('summary/chart', [TransactionSummaryController::class, 'chart']);
    Route::get('summary', [TransactionSummaryController::class, 'index']);
    Route::get('dangers', [DangerTransController::class, 'index']);
    Route::delete('dangers/{id}', [DangerTransController::class, 'destroy']);
    Route::post('dangers/{id}/checked', [DangerTransController::class, 'checked']);
    Route::post('dangers/batch-checked', [DangerTransController::class, 'batchChecked']);            
    
    Route::prefix('settle')->group(function() {
        Route::get('collect-withdraws/dangers', [CollectWithdrawController::class, 'danger']);
        Route::apiResource('collect-withdraws', CollectWithdrawController::class);
        
        Route::prefix('merchandises')->group(function() {
            Route::get('/', [MchtSettleController::class, 'index']);
            Route::get('/chart', [MchtSettleController::class, 'chart']);
            Route::post('/deduct', [MchtSettleController::class, 'deduct']);
            Route::get('/part', [MchtSettleController::class, 'part']);
            Route::get('/part/chart', [MchtSettleController::class, 'partChart']);
            Route::apiResource('cancel-deposits', CancelDepositController::class);
        });
        Route::prefix('salesforces')->group(function() {
            Route::get('/', [SalesSettleController::class, 'index']);
            Route::get('/chart', [SalesSettleController::class, 'chart']);
            Route::post('/deduct', [SalesSettleController::class, 'deduct']);
            Route::get('/part', [SalesSettleController::class, 'part']);
            Route::get('/part/chart', [SalesSettleController::class, 'partChart']);
        });
    });
    Route::prefix('settle-histories')->group(function() {
        Route::get('merchandises/chart', [MchtSettleHistoryController::class, 'chart']);
        Route::get('salesforces/chart', [SalesSettleHistoryController::class, 'chart']);
        Route::apiResource('merchandises', MchtSettleHistoryController::class);
        Route::apiResource('salesforces', SalesSettleHistoryController::class);
    });
    Route::get('realtime-histories', [RealtimeSendHistoryController::class, 'index']);
});
Route::apiResource('transactions', TransactionController::class);


