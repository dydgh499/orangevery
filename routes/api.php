<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\OperatorController;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\SalesforceController;
use App\Http\Controllers\Manager\TerminalController;
use App\Http\Controllers\Manager\PaymentModuleController;
use App\Http\Controllers\Manager\PaymentGatewayController;
use App\Http\Controllers\Manager\PaymentSectionController;
use App\Http\Controllers\Manager\ClassificationController;
use App\Http\Controllers\Manager\PostController;
use App\Http\Controllers\Manager\ComplaintController;
use App\Http\Controllers\Manager\TransactionController;


use App\Http\Controllers\Manager\SettleController;
use App\Http\Controllers\Log\FeeChangeHistoryController;
use App\Http\Controllers\Log\NotiSendHistoryController;
use App\Http\Controllers\Log\SettleHistoryController;
use App\Http\Controllers\Log\DangerTransController;
use App\Http\Controllers\Log\FailTransController;

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
            Route::get('fails', [FailTransController::class, 'index']);
            Route::get('dangers', [DangerTransController::class, 'index']);
            Route::get('settle/merchandises', [SettleController::class, 'index']);
            Route::get('settle/salesforces', [SettleController::class, 'index']);
            Route::get('settle-history/merchandises', [SettleHistoryController::class, 'index']);
            Route::get('settle-history/salesforces', [SettleHistoryController::class, 'index']);
        });
        Route::prefix('salesforces')->group(function() {
            Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'salesforce']);
            Route::get('classification', [SalesforceController::class, 'classification']);
        });
        Route::prefix('merchandises')->group(function() {            
            Route::get('terminals', [TerminalController::class, 'index']);   
            Route::apiResource('pay-modules', PaymentModuleController::class);   
            Route::post('fee-change-histories/{user}/{type}', [FeeChangeHistoryController::class, 'apply']);
            Route::get('fee-change-histories', [FeeChangeHistoryController::class, 'merchandise']);       
            Route::get('noti-send-histories', [NotiSendHistoryController::class, 'index']);
            Route::get('noti-send-histories/detail/{trans_id}', [NotiSendHistoryController::class, 'detail']);
        });
        Route::apiResource('complaints', ComplaintController::class);
        Route::apiResource('salesforces', SalesforceController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('merchandises', MerchandiseController::class);
        Route::apiResource('posts', PostController::class);
    });
    Route::post('upload-image', [PostController::class, 'uploadImage']);
});
