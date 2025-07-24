<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MomentoController;
use App\Http\Controllers\FotoController;

Route::prefix('v1')->group(function () {
    // Autenticação
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Rotas protegidas com Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Momentos CRUD, POST agora aceita fotos no mesmo request
        Route::get('/momentos', [MomentoController::class, 'index']);
        Route::post('/momentos', [MomentoController::class, 'store']);
        Route::put('/momentos/{id}', [MomentoController::class, 'update']);
        Route::delete('/momentos/{id}', [MomentoController::class, 'destroy']);

        // Fotos — apenas para listar e excluir fotos específicas se desejar
        Route::get('/momentos/{id}/fotos', [FotoController::class, 'index']);
        Route::delete('/fotos/{id}', [FotoController::class, 'destroy']);
    });

    // Opcional: rotas públicas para ver momentos públicos de um usuário
    Route::get('/usuarios/{id}/momentos', [MomentoController::class, 'publicosPorUsuario']);
    Route::get('/momentos/{id}', [MomentoController::class, 'show']);
});
