<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

/**
 * API Routes
 * Rutas privadas (utilizando middleware)
 */

//

/**
 * metodo nativo de laravel para utilizar los middleware
 */
Route::middleware(['system.jwt'])->group(function () {
    Route::post('/users/new', [UsersController::class, 'create']);
    Route::get('/users/getUser', [UsersController::class, 'getUser']);
    Route::post('/users/updateUser', [UsersController::class, 'updateEmail']);
});
