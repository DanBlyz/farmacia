<?php

use Illuminate\Support\Facades\Artisan;

test('siat:homologar fails on invalid etapa argument', function () {
    $exitCode = Artisan::call('siat:homologar', ['etapa' => 'etapa_invalida']);

    expect($exitCode)->toBe(1);
});

test('siat:sincronizar-catalogos fails on invalid catalog argument', function () {
    $exitCode = Artisan::call('siat:sincronizar-catalogos', ['catalogo' => 'InvalidoCatalogo']);

    expect($exitCode)->toBe(1);
});
