<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdatePayModuleController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateNotiUrlController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBankAccountController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateWithdrawBookController;

Route::middleware(['auth.update'])->group(function() {
        
    Route::prefix('merchandises/pay-modules/batch-updaters')->group(function() {       
        Route::post('set-payment-gateway', [BatchUpdatePayModuleController::class, 'setPaymentGateway']);
        Route::post('set-abnormal-trans-limit', [BatchUpdatePayModuleController::class, 'setAbnormalTransLimit']);
        Route::post('set-dupe-pay-count-validation', [BatchUpdatePayModuleController::class, 'setDupPayCountValidation']);
        Route::post('set-dupe-pay-least-validation', [BatchUpdatePayModuleController::class, 'setDupPayLeastValidation']);
        Route::post('set-mid', [BatchUpdatePayModuleController::class, 'setMid']);
        Route::post('set-tid', [BatchUpdatePayModuleController::class, 'setTid']);
        Route::post('set-pmid', [BatchUpdatePayModuleController::class, 'setPmid']);
        Route::post('set-api-key', [BatchUpdatePayModuleController::class, 'setApiKey']);
        Route::post('set-sub-key', [BatchUpdatePayModuleController::class, 'setSubKey']);
        Route::post('set-installment', [BatchUpdatePayModuleController::class, 'setInstallment']);
        Route::post('set-note', [BatchUpdatePayModuleController::class, 'setNote']);
        Route::post('set-pay-limit', [BatchUpdatePayModuleController::class, 'setPayLimit']);
        Route::post('set-pay-disable-time', [BatchUpdatePayModuleController::class, 'setForbiddenPayTime']);
        Route::post('set-filter-issuer', [BatchUpdatePayModuleController::class, 'setFilterIssuer']);
        Route::post('set-va-id', [BatchUpdatePayModuleController::class, 'setVaId']);
        Route::post('set-payment-term-min', [BatchUpdatePayModuleController::class, 'setPaymentTermMin']);
        Route::post('set-pay-window-secure-level', [BatchUpdatePayModuleController::class, 'setPayWindowSecureLevel']);
        Route::post('set-pay-window-extend-hour', [BatchUpdatePayModuleController::class, 'setPayWindowExtendHour']);
        Route::post('set-cxl-type', [BatchUpdatePayModuleController::class, 'setCxlType']);
        Route::post('set-pay-limit-type', [BatchUpdatePayModuleController::class, 'setPayLimitType']);
        Route::post('register', [BatchUpdatePayModuleController::class, 'register']);
        Route::delete('remove', [BatchUpdatePayModuleController::class, 'batchRemove']);   
    });

    Route::prefix('merchandises/noti-urls/batch-updaters')->group(function() {  
        Route::post('set-send-url', [BatchUpdateNotiUrlController::class, 'setSendUrl']);
        Route::post('set-noti-status', [BatchUpdateNotiUrlController::class, 'setNotiStatus']);
        Route::post('set-note', [BatchUpdateNotiUrlController::class, 'setNote']);
        Route::post('set-send-type', [BatchUpdateNotiUrlController::class, 'setSendType']);
        Route::delete('remove', [BatchUpdateNotiUrlController::class, 'batchRemove']);       
        Route::post('register', [BatchUpdateNotiUrlController::class, 'register']);
    });

    Route::middleware(['is.operate', 'last.login.ip'])->group(function() {
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
    
});
