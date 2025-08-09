<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MomentoController;
use App\Http\Controllers\FotoController;

Route::prefix('/v1')->group(function () {

    Route::prefix('/auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('/auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        Route::prefix('/user/me')->group(function () {
            Route::get('/', [AuthController::class, 'user']);
            Route::put('/', [AuthController::class, 'update']);
            Route::delete('/', [AuthController::class, 'destroy']);
        });

        Route::prefix('/momentos')->group(function () {
            Route::get('/me', [MomentoController::class, 'index']);
            Route::post('/', [MomentoController::class, 'store']);
            Route::put('/{id}', [MomentoController::class, 'update']);
            Route::delete('/{id}', [MomentoController::class, 'destroy']);
        });

    });
});
