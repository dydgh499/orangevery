<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\External\DeliveryAgencyController;
use App\Http\Controllers\Manager\Service\CMSTransactionController;

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

Route::prefix('v1')->group(function() {    
    Route::prefix('bonaejas')->group(function() {
            Route::post('mobile-code-issuance', [MessageController::class, 'mobileCodeIssuence']);
            Route::post('mobile-code-auth', [MessageController::class, 'mobileCodeAuth']);
        });        
    });
    Route::prefix('auth')->group(function() {
        Route::post('sign-in', [AuthController::class, 'signin']);
        Route::post('sign-up', [AuthController::class, 'signUp']);
        Route::post('2fa-qrcode/vertify', [AuthController::class, 'vertify2FA']);  

        Route::middleware(['auth:sanctum'])->group(function() {
            Route::post('sign-out', [AuthController::class, 'signout']);
            Route::post('owner-check', [CMSTransactionController::class, 'ownerCheck']);
        });
    }); 

    Route::middleware(['auth.delivery'])->group(function() {
        Route::prefix('delivery-agency')->group(function() {
            Route::post('sign-check', [DeliveryAgencyController::class, 'signCheck']);
            Route::post('sign-up', [DeliveryAgencyController::class, 'signUp']);
        });
    });
});
