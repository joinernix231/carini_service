<?php

namespace App\Providers;

use App\Exceptions\Handler;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
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
        //
    }
}
