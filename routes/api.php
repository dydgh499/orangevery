<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


/**
 * @group Manager API
 *
 * 관리자페이지에서 사용될 API 모음입니다.
 */
Route::prefix('v1')->group(function()
{
    Route::prefix('auth')->group(function() {
        Route::options('domain', [AuthController::class, 'DNSValidate']);
        Route::post('sign-in',[AuthController::class, 'signin']);
        Route::post('sign-up', [AuthController::class, 'signup']);
        Route::middleware('auth:sanctum')->post('sign-out',[AuthController::class, 'signout']);
    });
});



