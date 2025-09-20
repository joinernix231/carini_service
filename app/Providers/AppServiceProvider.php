<?php

namespace App\Providers;

use App\Exceptions\Handler;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
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
    }
}
