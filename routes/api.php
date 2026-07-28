<?php

use App\Http\Controllers\TramitesController;
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
    Route::post('/users/updateUser/{cedula}', [UsersController::class, 'updateEmail']);
    Route::get('/users/getByspk/{spkpersonas}', [UsersController::class, 'getUserByspkpersonas']);
    Route::get('/tramite/getBycedula/{cedula}', [TramitesController::class, 'traspasoByCedula']);
});
