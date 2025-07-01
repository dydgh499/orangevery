<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Manager\Transaction\TransactionSummaryController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateTransactionController;

use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Log\FailTransController;

Route::middleware(['auth.update'])->group(function() {
    Route::prefix('transactions')->group(function() {          
        Route::middleware(['is.operate'])->group(function() {
            Route::middleware(['is.edit.able'])->group(function() {   
                Route::prefix('batch-updaters')->group(function() {
                    Route::delete('remove', [BatchUpdateTransactionController::class, 'batchRemove']);
                    Route::post('change-settle-date', [BatchUpdateTransactionController::class, 'changeSettleDate']);
                    Route::post('set-custom-filter', [BatchUpdateTransactionController::class, 'setCustomFilter']);
                    Route::post('set-terminal-id', [BatchUpdateTransactionController::class, 'setTerminalId']);
                    Route::post('set-mid', [BatchUpdateTransactionController::class, 'setMid']);                
                    Route::post('set-tid', [BatchUpdateTransactionController::class, 'setTid']);                
                    Route::post('salesforces/set-fee', [BatchUpdateTransactionController::class, 'salesFeeApply']);      
                    Route::post('merchandises/set-fee', [BatchUpdateTransactionController::class, 'mchtFeeApply']);  
                    Route::post('merchandises/set-mcht', [BatchUpdateTransactionController::class, 'mchtApply']);  
                    Route::post('remove-deposit-fee', [BatchUpdateTransactionController::class, 'removeDepositFee']);
                });
            });
        });

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
        
    });
    Route::apiResource('transactions', TransactionController::class);
});
