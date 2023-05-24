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
use App\Http\Controllers\Manager\NoticeController;



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
        });

        Route::prefix('salesforces')->group(function() {
            Route::get('hierarchical-down', [SalesforceController::class, 'hierarchicalDown']);
            Route::get('hierarchical-up', [SalesforceController::class, 'hierarchicalUp']);
        });
        Route::apiResource('salesforces', SalesforceController::class);

        Route::prefix('merchandises')->group(function() {
            Route::apiResource('pay-modules', PaymentModuleController::class);
        });
        Route::prefix('transactions')->group(function() {
            Route::apiResource('dangers', NoticeController::class);
            Route::apiResource('settle/merchandises', NoticeController::class);
            Route::apiResource('settle/salesforces', NoticeController::class);
            Route::apiResource('settle-history/merchandises', NoticeController::class);
            Route::apiResource('settle-history/salesforces', NoticeController::class);
        });
        Route::apiResource('transactions', NoticeController::class);
        Route::apiResource('merchandises', MerchandiseController::class);
        Route::apiResource('posts', NoticeController::class);
    });
    Route::post('upload-image', [PaymentGatewayController::class, 'detail']);
});
