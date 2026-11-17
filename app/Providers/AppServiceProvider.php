<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Broadcasting\ExpoChannel;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            BaseHandler::class,
            Handler::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('x-forwarded-proto') === 'https' || config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        if (app()->environment('production')) {
            $this->app['request']->server->set('HTTPS', true);
        }

        // Límite de intentos de login. Con trustProxies('*') la IP sale de
        // X-Forwarded-For, que el cliente puede falsear para saltarse un límite
        // solo por IP; por eso también se limita por correo.
        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(10)->by('login-ip:' . $request->ip()),
                Limit::perMinute(5)->by('login-email:' . sha1($email)),
            ];
        });

        // Registrar canal Expo para notificaciones
        Notification::extend('expo', function ($app) {
            return $app->make(ExpoChannel::class);
        });
    }
}
