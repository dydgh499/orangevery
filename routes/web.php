<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
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

Route::get('{any?}', function() {
    $path = request()->path();
    if($path === 'login')
    {
        $brand = AuthController::getDNSInformation();      
        return view('application', ['brand'=>$brand]);  
    }
    else
        return view('application');  
})->where('any', '.*');
