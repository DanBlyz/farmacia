<?php

use App\Livewire\Sincronizacion\Catalogos;
use App\Models\TipoDocumentoSector;
use App\Models\User;
use Livewire\Livewire;

test('unauthorized users cannot visit the catalogos index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sincronizacion.catalogos'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the catalogos index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('sincronizacion.catalogos'))
        ->assertStatus(200)
        ->assertSeeLivewire(Catalogos::class);
});

test('can switch catalog tabs and reset pagination and search', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(Catalogos::class)
        ->assertSet('activeTab', 'TipoDocumentoSector')
        ->set('search', 'Sector')
        ->call('cambiarTab', 'TipoMoneda')
        ->assertSet('activeTab', 'TipoMoneda')
        ->assertSet('search', '');
});

test('can search catalog items in real time', function () {
    $user = User::factory()->admin()->create();

    TipoDocumentoSector::create([
        'codigo_clasificador' => '1',
        'descripcion' => 'FACTURA COMPRA VENTA',
    ]);

    TipoDocumentoSector::create([
        'codigo_clasificador' => '24',
        'descripcion' => 'NOTA DE CREDITO DEBITO',
    ]);

    Livewire::actingAs($user)
        ->test(Catalogos::class)
        ->set('search', 'NOTA DE CREDITO')
        ->assertSee('NOTA DE CREDITO DEBITO')
        ->assertDontSee('FACTURA COMPRA VENTA');
});

test('can change perPage option dynamically', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(Catalogos::class)
        ->assertSet('perPage', 10)
        ->set('perPage', 25)
        ->assertSet('perPage', 25);
});
