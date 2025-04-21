<?php

use App\Http\Middleware\ValidateUserToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            // Rutas web (sesiones, views, etc.)
            Route::middleware('api')
                ->group(base_path('routes/web.php'));

            // Rutas API (JSON, protegidas con token)
            Route::prefix('api')
                ->middleware(['api', ValidateUserToken::class])
                ->as('api.')
                ->group(base_path('routes/api.php'));
        },
        commands: base_path('routes/console.php'),
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Agregá aquí tus middleware globales si los necesitás
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Podés capturar excepciones personalizadas aquí
        // Ej: manejar UnauthorizedRequestException si querés
    })
    ->create();
