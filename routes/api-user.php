<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\Merchandise\BillKeyController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBillKeyController;

Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
        
    Route::prefix('merchandises')->group(function() {
        Route::middleware(['is.edit.able'])->delete('bill-keys/batch-updaters/remove', [BatchUpdateBillKeyController::class, 'batchRemove']);

        Route::get('pay-modules/chart', [PaymentModuleController::class, 'chart']);
        Route::get('pay-modules/all', [PaymentModuleController::class, 'all']);
            Route::prefix('pay-modules')->group(function() {
                Route::apiResource('bill-keys', BillKeyController::class); 
            });
        });   
    Route::get('bill-keys', [BillKeyController::class, 'managerIndex']); 
    
    Route::apiResource('pay-modules', PaymentModuleController::class);
});

