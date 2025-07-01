<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\Merchandise\NotiUrlController;
use App\Http\Controllers\Manager\Merchandise\BillKeyController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBillKeyController;

use App\Http\Controllers\Log\NotiSendHistoryController;

Route::middleware(['auth.update'])->group(function() {
        
    Route::prefix('merchandises')->group(function() {
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
        Route::get('bill-keys', [BillKeyController::class, 'managerIndex']); 
        Route::get('noti-urls/chart', [NotiUrlController::class, 'chart']);
        
        Route::apiResource('pay-modules', PaymentModuleController::class);
        Route::apiResource('noti-urls', NotiUrlController::class);
    });
});
