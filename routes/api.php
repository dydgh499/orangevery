<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\BrandController;
use App\Http\Controllers\Manager\OperatorController;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Http\Controllers\Manager\DeviceController;
use App\Http\Controllers\Manager\CategoryController;
use App\Http\Controllers\Manager\ProductController;
use App\Http\Controllers\Manager\OptionController;
use App\Http\Controllers\Manager\CouponModelController;
use App\Http\Controllers\Manager\CouponPublishController;
use App\Http\Controllers\Manager\StampController;
use App\Http\Controllers\Manager\OrderController;
use App\Http\Controllers\Manager\PointController;
use App\Http\Controllers\Manager\AdvertisementController;
use App\Http\Controllers\Manager\NotificationController;
use App\Http\Controllers\Manager\NoticeController;

use App\Http\Controllers\AuthController;

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
        Route::apiResource('points', PointController::class);
        Route::apiResource('devices', DeviceController::class);
        Route::apiResource('categorys', CategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('options', OptionController::class);
        Route::apiResource('couponModels', CouponModelController::class);
        Route::apiResource('couponPublishs', CouponPublishController::class);
        Route::apiResource('stamps', StampController::class);
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('advertisements', AdvertisementController::class);
        Route::apiResource('notifications', NotificationController::class);
        Route::apiResource('notices', NoticeController::class);
    });
});
