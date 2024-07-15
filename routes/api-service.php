<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\Service\BeforeBrandInfoController;
use App\Http\Controllers\Manager\Service\AbnormalConnectionController;
use App\Http\Controllers\Manager\Service\DifferentSettlementInfoController;

use App\Http\Controllers\Manager\Service\MchtBlacklistController;
use App\Http\Controllers\Manager\Service\HeadOfficeAccountController;
use App\Http\Controllers\Manager\Service\ClassificationController;
use App\Http\Controllers\Manager\Service\OperatorIPController;
use App\Http\Controllers\Manager\Service\HolidayController;

use App\Http\Controllers\Manager\OperatorController;
use App\Http\Controllers\Manager\FinanceVanController;

use App\Http\Controllers\Manager\PaymentGatewayController;
use App\Http\Controllers\Manager\PaymentSectionController;

use App\Http\Controllers\Manager\PostController;
use App\Http\Controllers\Manager\PopupController;
use App\Http\Controllers\Manager\ComplaintController;
use App\Http\Controllers\Log\OperatorHistoryContoller;

Route::get('services/pay-gateways/detail', [PaymentGatewayController::class, 'detail']);
Route::get('popups/currently', [PopupController::class, 'currently']);
Route::get('posts/{id}/parent', [PostController::class, 'parent']);
Route::get('posts/recent', [PostController::class, 'recent']);

Route::middleware(['is.operate', 'last.login.ip'])->group(function() {
    Route::middleware(['is.edit.able'])->post('posts/upload', [PostController::class, 'upload']);  
    Route::prefix('services')->group(function() {
        Route::get('bonaejas', [MessageController::class, 'index']);
        Route::get('bonaejas/chart', [MessageController::class, 'chart']);
        Route::get('brands/chart', [BrandController::class, 'chart']);
        Route::get('abnormal-connection-histories/secure-report', [AbnormalConnectionController::class, 'secureReport']);
        Route::get('abnormal-connection-histories/secure-report/detail-work-status', [AbnormalConnectionController::class, 'detailWorkStatus']);
        Route::get('abnormal-connection-histories', [AbnormalConnectionController::class, 'index']);

        Route::middleware(['is.edit.able'])->group(function() {
            Route::post('operators/{id}/password-change', [OperatorController::class, 'passwordChange']);
            Route::post('operators/{id}/unlock-account', [OperatorController::class, 'unlockAccount']);  
            Route::post('operators/{id}/2fa-qrcode', [OperatorController::class, 'create2FAQRLink']);  
            Route::post('operators/{id}/2fa-qrcode/create-vertify', [OperatorController::class, 'vertify2FAQRLink']);

            Route::post('mcht-blacklists/bulk-register', [MchtBlacklistController::class, 'bulkRegister']);            
            Route::post('holidays/bulk-register', [HolidayController::class, 'updateHolidays']);
        });
    
        Route::apiResource('brands/before-brand-infos', BeforeBrandInfoController::class);
        Route::apiResource('brands/different-settlement-infos', DifferentSettlementInfoController::class);
        
        Route::middleware(['dev.ip'])->apiResource('brands/operator-ips', OperatorIPController::class);
        Route::apiResource('brands', BrandController::class);
    
        Route::apiResource('operators', OperatorController::class);            
        Route::apiResource('operator-histories', OperatorHistoryContoller::class);
        Route::apiResource('pay-gateways', PaymentGatewayController::class);
        Route::apiResource('pay-sections', PaymentSectionController::class);
        Route::apiResource('finance-vans', FinanceVanController::class);
        Route::apiResource('classifications', ClassificationController::class);
        Route::apiResource('mcht-blacklists', MchtBlacklistController::class);            
        Route::apiResource('holidays', HolidayController::class);
        Route::get('head-office-accounts', [HeadOfficeAccountController::class, 'index']);
        Route::get('head-office-accounts/all', [HeadOfficeAccountController::class, 'all']);
        
    });
    Route::apiResource('popups', PopupController::class);
});

Route::apiResource('complaints', ComplaintController::class);
Route::apiResource('posts', PostController::class);
