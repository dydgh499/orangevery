<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBankAccountController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateWithdrawBookController;
use App\Http\Controllers\Manager\BatchUpdater\BatchTransactionController;

Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
    Route::prefix('owner-check/batch-updaters')->group(function() { 
        Route::post('register', [BatchUpdateBankAccountController::class, 'register']);
    });
    
    Route::prefix('bank-accounts/batch-updaters')->group(function() { 
        Route::delete('remove', [BatchUpdateBankAccountController::class, 'batchRemove']);
        Route::post('owner-check', [BatchUpdateBankAccountController::class, 'ownerCheck']);
    });

    Route::prefix('bulk-withdraws/batch-updaters')->group(function() { 
        Route::post('register', [BatchUpdateWithdrawBookController::class, 'register']);
        Route::delete('remove', [BatchUpdateWithdrawBookController::class, 'batchRemove']);
    });
    Route::prefix('pays/transactions/batch-updaters')->group(function() { 
        Route::post('register', [BatchTransactionController::class, 'register']);
    });
});
