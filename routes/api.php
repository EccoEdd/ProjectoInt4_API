<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IncubatorController;

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

    Route::middleware(['auth:sanctum', 'status'])->prefix('incubator')->group(function(){
        Route::post('addInc', [IncubatorController::class, 'addIncubator']);
        Route::get('getData', [IncubatorController::class, 'showAllIncubators']);
        Route::post('addVi', [IncubatorController::class, 'addVisitor']);
    });

    Route::prefix('user')->group(function(){
        Route::post('register', [UserController::class, 'newUser']);
        Route::post('logIn', [UserController::class, 'logIn']);

        Route::middleware('auth:sanctum')->group(function(){
            Route::delete('logOut', [UserController::class, 'logOut']);
            Route::get('info', [UserController::class, 'userInformation'])
                ->middleware('status');

            Route::delete('delete/{id}', [UserController::class, 'deleteUserData'])
                ->where('id', '[0-9]+')
                ->middleware('rol:a');
        });
    });
    Route::prefix('actions')->group(function(){
        Route::get('/verify/{id}', [UserController::class, 'verifyUser'])
            ->name('verify')
            ->middleware('signed')
            ->where('id', '[0-9]+');
    });
});
