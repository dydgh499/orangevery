<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::view('password', 'password')->name('password');
Route::prefix('docs')->middleware('auth.docs')->group(function() {
    Route::any('{corp}', function($corp) {
        $viewPath = "scribe.$corp";
        if (view()->exists($viewPath)) {
            return view($viewPath);
        }
        return view('scribe.{corp}');
    });
});
Route::get('{any}', [AuthController::class, 'domain'])->where('any','.*');
Route::post('/transactions/pay/result', [AuthController::class, 'domain'])->where('any','.*');
Route::post('/transactions/pay-test/result', [AuthController::class, 'domain'])->where('any','.*');
