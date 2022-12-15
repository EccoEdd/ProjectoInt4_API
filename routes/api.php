<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IncubatorController;
use App\Http\Controllers\HumidityController;
use App\Http\Controllers\TemperatureController;
use App\Http\Controllers\DioxideController;
use App\Http\Controllers\OwnershipController;

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
    Route::get('last', [TemperatureController::class, 'lastData']);

    Route::get('temperature/last/{id}', [TemperatureController::class, 'temperatureById'])
        ->where('id', '[0-9]+');

    Route::middleware(['auth:sanctum', 'status'])->prefix('incubator')->group(function(){
        Route::post('addInc', [IncubatorController::class, 'addIncubator']);
        Route::get('getData', [IncubatorController::class, 'showAllIncubators']);

        Route::get('/{id}', [IncubatorController::class, 'showIncubator'])
            ->where('id', '[0-9]+');

        Route::delete('delete', [IncubatorController::class, 'deleteIncubator']);

        Route::get('visitors', [IncubatorController::class, 'showVisitors']);
        Route::post('addVi', [IncubatorController::class, 'addVisitor']);
        Route::post('removeVi', [IncubatorController::class, 'removeVisitor']);

        Route::get('role', [OwnershipController::class, 'checkOwnership']);
        Route::get('admin', [OwnershipController::class, 'checkAdmin']);
        Route::get('visitor', [OwnershipController::class, 'checkVisitor']);
    });

    Route::middleware(['auth:sanctum', 'status'])->prefix('data')->group(function(){
        Route::prefix('humidity')->group(function (){
           Route::get('last', [HumidityController::class, 'lastHumidityData']);
           Route::get('/', [HumidityController::class, 'humidityData']);
        });
        Route::prefix('temperature')->group(function (){
            Route::get('last', [TemperatureController::class, 'lastTemperatureData']);
            Route::get('/', [TemperatureController::class, 'temperatureData']);
        });
        Route::prefix('dioxide')->group(function(){
            Route::get('last', [DioxideController::class, 'lastDioxideData']);
            Route::get('/', [DioxideController::class, 'dioxideData']);
        });
        Route::get('/{id}', [IncubatorController::class, 'allDataDunno'])
            ->where('id', '[0-9]+');
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
            Route::get('/', [UserController::class, 'allUsers'])
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
