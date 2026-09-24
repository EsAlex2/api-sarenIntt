<?php

use App\Http\Controllers\TramitesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

/**
 * API Routes
 * Rutas privadas (utilizando middleware)
 */


/**
 * metodo nativo de laravel para utilizar los middleware
 */
// Route::middleware(['system.jwt'])->group(function () {
    Route::prefix('/users')->group(function () {
        Route::post('/new', [UsersController::class, 'create']);
        Route::post('/updateUser/{cedula}', [UsersController::class, 'updateEmail']);
        Route::get('/getUser/{cedula}', [UsersController::class, 'get']);
    });
    Route::prefix('/tramite')->group(function () {
        Route::get('/getBycedula/{cedula}', [TramitesController::class, 'traspasoByCedula']);
        Route::post('/new', [TramitesController::class, 'create']);
    });
// });
