<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::prefix('docs')->middleware('auth.docs')->group(function() {    
    Route::get('{any}', view('scribe.index'));
});
Route::get('{any}', [AuthController::class, 'domain'])->where('any','.*');
