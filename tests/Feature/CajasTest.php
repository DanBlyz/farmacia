<?php

use App\Livewire\Cajas\ControlCajas;
use App\Models\Caja;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

test('unauthorized users cannot visit the cajas control page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('cajas.control'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the cajas control page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('cajas.control'))
        ->assertStatus(200)
        ->assertSeeLivewire(ControlCajas::class);
});

test('can open a new cash register shift automatically taking user assigned sucursal and punto de venta', function () {
    $sucursal = Sucursal::factory()->create();
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Punto Venta Test 1',
        'descripcion' => 'PV 1',
        'codigo' => 1,
        'estado' => 'ACTIVO',
    ]);
    $user = User::factory()->admin()->create(['punto_venta_id' => $puntoVenta->id]);

    Livewire::actingAs($user)
        ->test(ControlCajas::class)
        ->call('abrirModalApertura')
        ->assertSet('showAperturaModal', true)
        ->assertSet('aperturaSucursalId', $sucursal->id)
        ->assertSet('aperturaPuntoVentaId', $puntoVenta->id)
        ->set('montoApertura', 150.00)
        ->set('descripcionApertura', 'Inicio de turno matutino')
        ->call('guardarApertura')
        ->assertSet('showAperturaModal', false)
        ->assertHasNoErrors();

    $caja = Caja::where('usuario_id', $user->id)->where('estado', 'ABIERTA')->first();
    expect($caja)->not->toBeNull()
        ->and((float) $caja->monto_apertura)->toBe(150.00)
        ->and((float) $caja->saldo)->toBe(150.00)
        ->and($caja->sucursal_id)->toBe($sucursal->id)
        ->and($caja->punto_venta_id)->toBe($puntoVenta->id);
});

test('cannot open more than one active cash register shift for the same user', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Punto Venta Test 2',
        'codigo' => 2,
        'estado' => 'ACTIVO',
    ]);

    Caja::factory()->create([
        'usuario_id' => $user->id,
        'estado' => 'ABIERTA',
    ]);

    Livewire::actingAs($user)
        ->test(ControlCajas::class)
        ->call('abrirModalApertura')
        ->assertSet('showAperturaModal', false); // No se abre modal porque ya tiene caja activa
});

test('can record extraordinary cash movements (income and expense)', function () {
    $user = User::factory()->admin()->create();
    $caja = Caja::factory()->create([
        'usuario_id' => $user->id,
        'monto_apertura' => 100.00,
        'total_ingreso' => 100.00,
        'saldo' => 100.00,
        'estado' => 'ABIERTA',
    ]);

    // 1. Ingreso menor
    Livewire::actingAs($user)
        ->test(ControlCajas::class)
        ->call('abrirModalMovimiento')
        ->assertSet('showMovimientoModal', true)
        ->set('tipoMovimientoCaja', 'INGRESO')
        ->set('montoMovimientoCaja', 50.00)
        ->set('motivoMovimientoCaja', 'Sencillo extra ingresado por tesorería')
        ->call('guardarMovimiento')
        ->assertSet('showMovimientoModal', false)
        ->assertHasNoErrors();

    expect((float) $caja->fresh()->otro_ingreso)->toBe(50.00)
        ->and((float) $caja->fresh()->saldo)->toBe(150.00);

    // 2. Egreso menor
    Livewire::actingAs($user)
        ->test(ControlCajas::class)
        ->call('abrirModalMovimiento')
        ->set('tipoMovimientoCaja', 'SALIDA')
        ->set('montoMovimientoCaja', 20.00)
        ->set('motivoMovimientoCaja', 'Compra de agua y bolsas para mostrador')
        ->call('guardarMovimiento')
        ->assertSet('showMovimientoModal', false)
        ->assertHasNoErrors();

    expect((float) $caja->fresh()->total_salida)->toBe(20.00)
        ->and((float) $caja->fresh()->saldo)->toBe(130.00);
});

test('can perform closure and reconciliation of cash register with difference calculation', function () {
    $user = User::factory()->admin()->create();
    $caja = Caja::factory()->create([
        'usuario_id' => $user->id,
        'monto_apertura' => 200.00,
        'venta_contado' => 150.00,
        'otro_ingreso' => 0,
        'total_salida' => 0,
        'saldo' => 350.00,
        'estado' => 'ABIERTA',
    ]);

    Livewire::actingAs($user)
        ->test(ControlCajas::class)
        ->call('abrirModalCierre', $caja->id)
        ->assertSet('showCierreModal', true)
        ->set('montoCierreEfectivo', 355.00) // 5 Bs sobrante
        ->set('descripcionCierre', 'Cierre conforme con leve sobrante')
        ->call('guardarCierre')
        ->assertSet('showCierreModal', false)
        ->assertHasNoErrors();

    $cajaActualizada = $caja->fresh();
    expect($cajaActualizada->estado)->toBe('CERRADA')
        ->and((float) $cajaActualizada->monto_cierre)->toBe(355.00)
        ->and($cajaActualizada->fecha_cierre)->not->toBeNull()
        ->and($cajaActualizada->descripcion_cierre)->toContain('Sobrante');
});
