<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBankAccountController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateWithdrawBookController;

Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
    Route::prefix('owner-check/batch-updaters')->group(function() { 
        Route::post('register', [BatchUpdateBankAccountController::class, 'ownerCheckHard']);
    });
    
    Route::prefix('bank-accounts/batch-updaters')->group(function() { 
        Route::delete('remove', [BatchUpdateBankAccountController::class, 'batchRemove']);
    });

    Route::prefix('bulk-withdraws/batch-updaters')->group(function() { 
        Route::post('register', [BatchUpdateWithdrawBookController::class, 'withdrawNoAccount']);
        Route::delete('remove', [BatchUpdateWithdrawBookController::class, 'batchRemove']);
    });
});
