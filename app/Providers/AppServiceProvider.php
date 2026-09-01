<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Dedoc\Scramble\Scramble::routes(function (\Illuminate\Routing\Route $route) {
            return \Illuminate\Support\Str::startsWith($route->uri, 'users') 
                || \Illuminate\Support\Str::startsWith($route->uri, 'tramite');
        });
    }
}
