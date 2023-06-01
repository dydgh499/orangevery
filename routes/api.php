<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\OperatorController;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\SalesforceController;
use App\Http\Controllers\Manager\PaymentModuleController;
use App\Http\Controllers\Manager\PaymentGatewayController;
use App\Http\Controllers\Manager\PaymentSectionController;
use App\Http\Controllers\Manager\ClassificationController;
use App\Http\Controllers\Manager\NoticeController;

use App\Http\Controllers\Manager\DangerController;
use App\Http\Controllers\Manager\SettleController;
use App\Http\Controllers\Manager\SettleHistoryController;

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
    Route::prefix('auth')->group(function() {
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::middleware('auth:sanctum')->post('sign-out', [AuthController::class, 'signout']);
        Route::middleware('auth:sanctum')->post('ok', [AuthController::class, 'ok']);
    });
    Route::prefix('manager')->middleware('auth:sanctum')->group(function() {
        Route::prefix('services')->group(function() {
            Route::prefix('pay-gateways')->group(function() {
                Route::get('detail', [PaymentGatewayController::class, 'detail']);
            });
            Route::apiResource('brands', BrandController::class);
            Route::apiResource('operators', OperatorController::class);
            Route::apiResource('pay-gateways', PaymentGatewayController::class);
            Route::apiResource('pay-sections', PaymentSectionController::class);
            Route::apiResource('classifications', ClassificationController::class);
        });
        Route::prefix('transactions')->group(function() {
            Route::get('dangers', [DangerController::class, 'index']);
            Route::get('settle/merchandises', [SettleController::class, 'index']);
            Route::get('settle/salesforces', [SettleController::class, 'index']);
            Route::get('settle-history/merchandises', [SettleHistoryController::class, 'index']);
            Route::get('settle-history/salesforces', [SettleHistoryController::class, 'index']);
        });
        Route::prefix('salesforces')->group(function() {
            Route::apiResource('fee-change-histories', PaymentModuleController::class);
            Route::get('classification', [SalesforceController::class, 'classification']);
        });
        Route::prefix('merchandises')->group(function() {
            Route::apiResource('pay-modules', PaymentModuleController::class);   
            Route::get('fee-change-histories', [PaymentModuleController::class, 'index']);       
            Route::get('noti-send-histories', [PaymentModuleController::class, 'index']);     
        });
        Route::apiResource('complaints', NoticeController::class);
        Route::apiResource('salesforces', SalesforceController::class);
        Route::apiResource('transactions', NoticeController::class);
        Route::apiResource('merchandises', MerchandiseController::class);
        Route::apiResource('posts', NoticeController::class);
    });
    Route::post('upload-image', [PaymentGatewayController::class, 'detail']);
});
