<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   return response()->json([
        'message' => 'Bienvenido a la API de Saren',
        'version' => phpversion(),
        'laravel' => app()->version()
    ]);
});
