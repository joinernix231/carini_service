<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Programar verificación de mantenimientos sin confirmar cada 5 minutos
Schedule::command('maintenances:check-unconfirmed')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
