<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\OperatorController;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\SalesforceController;
use App\Http\Controllers\Manager\PaymentModuleController;
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


Route::prefix('v1')->middleware('log.route')->group(function()
{
    Route::prefix('auth')->group(function() {
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::middleware('auth:sanctum')->post('sign-out', [AuthController::class, 'signout']);
        Route::middleware('auth:sanctum')->post('ok', [AuthController::class, 'ok']);
    });
    Route::prefix('manager')->middleware('auth:sanctum')->group(function() {
        Route::apiResource('brands', BrandController::class);
        Route::apiResource('operators', OperatorController::class);
        Route::apiResource('merchandises', MerchandiseController::class);
        Route::apiResource('salesforces', SalesforceController::class);
        Route::apiResource('pay-modules', PaymentModuleController::class);
        Route::apiResource('pay-sections', PaymentSectionController::class);
        Route::apiResource('notices', NoticeController::class);
    });
});
