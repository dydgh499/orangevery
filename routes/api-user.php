<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\Pay\BillKeyController;
use App\Http\Controllers\Manager\Pay\PaymentModuleController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBillKeyController;

Route::middleware(['auth.update', 'is.operate', 'last.login.ip'])->group(function() {
    Route::prefix('pays')->group(function() {
        Route::middleware(['is.edit.able'])->delete('bill-keys/batch-updaters/remove', [BatchUpdateBillKeyController::class, 'batchRemove']);
        Route::apiResource('bill-keys', BillKeyController::class); 
        Route::get('pay-modules/chart', [PaymentModuleController::class, 'chart']);
        Route::get('pay-modules/all', [PaymentModuleController::class, 'all']);
        Route::apiResource('pay-modules', PaymentModuleController::class);
    });
});

