<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MomentoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PerfilController;

Route::prefix('/v1')->group(function () {

    Route::prefix('/auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);                               //
        Route::post('/login', [AuthController::class, 'login']);                                     //
    });

    Route::get('/{username}', [PerfilController::class, 'index']);                                   //

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('/auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);                               //
        });

        Route::prefix('/user/me')->group(function () {
            Route::get('/', [AuthController::class, 'user']);                                        //
            Route::put('/', [AuthController::class, 'update']);                                      //
            Route::delete('/', [AuthController::class, 'destroy']);                                  //
            Route::get('/avatar', [AuthController::class, 'avatar']);                                //
        });

        Route::prefix('/momentos')->group(function () {
            Route::get('/me', [MomentoController::class, 'index']);                                  //
            Route::post('/', [MomentoController::class, 'store']);                                   //
            Route::put('/{id}', [MomentoController::class, 'update']);
            Route::delete('/{id}', [MomentoController::class, 'destroy']);

            Route::post('/{momentoId}/comments', [CommentController::class, 'store']);               //
            Route::get('/{momentoId}/comments', [CommentController::class, 'index']);                //
            Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);           //

            Route::post('/{momentoId}/like', [MomentoController::class, 'like']);                    //
            Route::post('/{momentoId}/unlike', [MomentoController::class, 'unlike']);                //
        });

        Route::prefix('/user')->group(function () {
            Route::post('/{username}/follow', [UserController::class, 'follow']);                    //
            Route::post('/{username}/unfollow', [UserController::class, 'unfollow']);                //
            Route::get('/{username}/followers', [UserController::class, 'followers']);               //
            Route::get('/{username}/following', [UserController::class, 'following']);               //

            Route::put('/privacy', [UserController::class, 'updatePrivacy']);
        });

        Route::prefix('/notifications')->group(function () {
            Route::get('/', [UserController::class, 'notifications']);                               //
            Route::post('/{notificationId}/read', [UserController::class, 'markNotificationAsRead']);//
        });
    });
});
