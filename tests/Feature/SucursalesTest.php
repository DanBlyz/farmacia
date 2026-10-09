<?php

use App\Livewire\Administracion\Sucursales;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can visit the sucursales index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('administracion.sucursales'))
        ->assertStatus(200)
        ->assertSeeLivewire(Sucursales::class);
});

test('can create a new sucursal and close modal', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('codigo_sucursal', 'SUC-CENTRAL')
        ->set('nombre', 'Farmacia Central')
        ->set('direccion', 'Av. Prado #123')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('sucursales', [
        'codigo_sucursal' => 'SUC-CENTRAL',
        'nombre' => 'Farmacia Central',
        'direccion' => 'Av. Prado #123',
        'usuario_creador_id' => $user->id,
    ]);
});

test('can search sucursales by name or code', function () {
    $user = User::factory()->create();

    Sucursal::create(['codigo_sucursal' => 'SUC-001', 'nombre' => 'Sucursal Miraflores']);
    Sucursal::create(['codigo_sucursal' => 'SUC-002', 'nombre' => 'Sucursal Calacoto']);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->set('search', 'Miraflores')
        ->assertSee('Sucursal Miraflores')
        ->assertDontSee('Sucursal Calacoto');
});

test('can edit an existing sucursal', function () {
    $user = User::factory()->create();
    $sucursal = Sucursal::create([
        'codigo_sucursal' => 'SUC-OLD',
        'nombre' => 'Sucursal Antigua',
        'direccion' => 'Calle 1',
    ]);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('edit', $sucursal->id)
        ->assertSet('sucursalId', $sucursal->id)
        ->assertSet('nombre', 'Sucursal Antigua')
        ->set('nombre', 'Sucursal Renovada')
        ->set('codigo_sucursal', 'SUC-NEW')
        ->call('save')
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('sucursales', [
        'id' => $sucursal->id,
        'codigo_sucursal' => 'SUC-NEW',
        'nombre' => 'Sucursal Renovada',
        'usuario_modificador_id' => $user->id,
    ]);
});

test('can soft delete a sucursal', function () {
    $user = User::factory()->create();
    $sucursal = Sucursal::create([
        'codigo_sucursal' => 'SUC-DEL',
        'nombre' => 'Sucursal Por Eliminar',
    ]);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('delete', $sucursal->id);

    $this->assertSoftDeleted('sucursales', [
        'id' => $sucursal->id,
        'usuario_eliminador_id' => $user->id,
    ]);
});

test('can open and close puntos de venta siat modal', function () {
    $user = User::factory()->create();
    $sucursal = Sucursal::create([
        'codigo_sucursal' => '0',
        'nombre' => 'Sucursal Para Facturacion',
    ]);
    \App\Models\PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'codigo' => 0,
        'nombre' => 'Punto de Venta 0',
        'tipo' => 'VENTANILLA',
        'codigo_ambiente' => 2,
    ]);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('openPuntosVentaModal', $sucursal->id)
        ->assertSet('showPuntosVentaModal', true)
        ->assertSet('selectedSucursalId', $sucursal->id)
        ->assertSee('Puntos de Venta (Facturación en Línea SIAT)')
        ->assertSee('Punto de Venta 0')
        ->call('closePuntosVentaModal')
        ->assertSet('showPuntosVentaModal', false)
        ->assertSet('selectedSucursalId', null);
});

test('sincronizar puntos de venta shows error when no valid cuis exists', function () {
    $user = User::factory()->create();
    $sucursal = Sucursal::create([
        'codigo_sucursal' => '10',
        'nombre' => 'Sucursal Sin Cuis para Sincronizar PV',
    ]);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('openPuntosVentaModal', $sucursal->id)
        ->assertSee('Sincronización Punto de Ventas')
        ->call('sincronizarPuntosVenta', $sucursal->id)
        ->assertDispatched('swal-alert');
});

test('cierre operaciones sistema shows error when no valid cuis exists', function () {
    $user = User::factory()->create();
    $sucursal = Sucursal::create([
        'codigo_sucursal' => '11',
        'nombre' => 'Sucursal Sin Cuis para Cierre Operaciones',
    ]);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('openPuntosVentaModal', $sucursal->id)
        ->assertSee('Cierre Operaciones de Sistema')
        ->call('cierreOperacionesSistema', $sucursal->id)
        ->assertDispatched('swal-alert');
});

test('can open and validate registro punto venta modal and return to puntos de venta modal on close', function () {
    $user = User::factory()->create();
    $sucursal = Sucursal::create([
        'codigo_sucursal' => '12',
        'nombre' => 'Sucursal Para Registro PV',
    ]);

    Livewire::actingAs($user)
        ->test(Sucursales::class)
        ->call('openPuntosVentaModal', $sucursal->id)
        ->assertSet('showPuntosVentaModal', true)
        ->call('openRegistroPuntoVentaModal', $sucursal->id)
        ->assertSet('showPuntosVentaModal', false)
        ->assertSet('showRegistroPuntoVentaModal', true)
        ->assertSee('Registro de Punto de Venta SIAT')
        ->call('guardarRegistroPuntoVenta')
        ->assertHasErrors(['newPuntoVentaNombre', 'newPuntoVentaDescripcion', 'newPuntoVentaTipoId'])
        ->call('closeRegistroPuntoVentaModal')
        ->assertSet('showRegistroPuntoVentaModal', false)
        ->assertSet('showPuntosVentaModal', true);
});
