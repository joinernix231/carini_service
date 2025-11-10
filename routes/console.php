<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Programar verificación de mantenimientos sin confirmar cada minuto
Schedule::command('maintenances:check-unconfirmed')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
