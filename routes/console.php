<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronización automática de catálogos SIAT en producción (día 1 de cada mes a las 02:00 AM)
Schedule::command('siat:sincronizar-catalogos')
    ->monthlyOn(1, '02:00')
    ->name('siat_sincronizar_catalogos_mensual');
