<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
|
*/

// Rutas públicas para verificación y registro de usuarios
Route::get('/users/verify/{documento}', [UserController::class, 'verify']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/users/update-email', [UserController::class, 'updateEmail']);

// Rutas protegidas que utilizan el middleware personalizado de JWT
Route::middleware('auth.jwt')->group(function () {
    Route::get('/users/me', [UserController::class, 'me']);
});
