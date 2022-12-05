<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function(){
    Route::prefix('user')->group(function(){
        Route::post('register', [UserController::class, 'newUser']);
        Route::post('logIn', [UserController::class, 'logIn']);
        Route::delete('logOut', [UserController::class, 'logOut'])
        ->middleware('auth:sanctum');
    });
    Route::prefix('actions')->group(function(){
        Route::get('/verify/{id}', [UserController::class, 'verifyUser'])
            ->name('verify')
            ->middleware('signed')
            ->where('id', '[0-9]+');
    });
});
