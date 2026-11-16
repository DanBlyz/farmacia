<?php

use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\Factura;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\User;

beforeEach(function () {
    $this->user = User::firstOrCreate(['id' => 1], [
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'Administrador',
        'estado' => 'ACTIVO',
    ]);

    $this->sucursal = Sucursal::firstOrCreate(['id' => 1], [
        'nombre' => 'Casa Matriz Sopocachi',
        'codigo_sucursal' => 0,
        'direccion' => 'Av. Central 123',
        'estado' => 'ACTIVO',
    ]);

    $this->pv0 = PuntoVenta::firstOrCreate(['codigo' => 0, 'sucursal_id' => $this->sucursal->id], [
        'nombre' => 'Punto de Venta 0',
        'estado' => 'ACTIVO',
    ]);

    $this->pv1 = PuntoVenta::firstOrCreate(['codigo' => 1, 'sucursal_id' => $this->sucursal->id], [
        'nombre' => 'Punto de Venta 1',
        'estado' => 'ACTIVO',
    ]);

    Cufd::firstOrCreate(['punto_venta_id' => $this->pv0->id, 'sucursal_id' => $this->sucursal->id], [
        'codigo' => 'CUFDTESTPV0123456789ABCDEF012345',
        'codigo_control' => 'CTRL01',
        'direccion' => 'Av. Central 123',
        'fecha_vigencia' => now()->addDay(),
        'estado' => 'ACTIVO',
    ]);

    Cuis::firstOrCreate(['punto_venta_id' => $this->pv0->id, 'sucursal_id' => $this->sucursal->id], [
        'codigo' => 'CUISPV0123',
        'fecha_vigencia' => now()->addYear(),
        'estado' => 'ACTIVO',
    ]);

    Cufd::firstOrCreate(['punto_venta_id' => $this->pv1->id, 'sucursal_id' => $this->sucursal->id], [
        'codigo' => 'CUFDTESTPV1123456789ABCDEF012345',
        'codigo_control' => 'CTRL02',
        'direccion' => 'Av. Central 123',
        'fecha_vigencia' => now()->addDay(),
        'estado' => 'ACTIVO',
    ]);

    Cuis::firstOrCreate(['punto_venta_id' => $this->pv1->id, 'sucursal_id' => $this->sucursal->id], [
        'codigo' => 'CUISPV1123',
        'fecha_vigencia' => now()->addYear(),
        'estado' => 'ACTIVO',
    ]);
});

test('generar facturas command creates invoices in database with details and valid state', function () {
    $conteoInicial = Factura::count();

    $this->artisan('siat:generar-facturas', [
        '--cantidad' => 2,
        '--pv' => 0,
    ])
        ->expectsOutputToContain('SIAT BOLIVIA - GENERADOR DE FACTURAS EN LÍNEA')
        ->expectsOutputToContain('Total generadas: 2')
        ->assertExitCode(0);

    expect(Factura::count())->toBe($conteoInicial + 2);

    $ultimaFactura = Factura::latest('id')->first();
    expect($ultimaFactura->estado)->toBe('VALIDADA')
        ->and($ultimaFactura->detalles)->not->toBeEmpty()
        ->and($ultimaFactura->pagos)->not->toBeEmpty();
});

test('generar facturas command supports pv option with code 1', function () {
    $this->artisan('siat:generar-facturas', [
        '--cantidad' => 1,
        '--pv' => 1,
    ])
        ->expectsOutputToContain('Punto de Venta : PV 1')
        ->assertExitCode(0);

    $ultimaFactura = Factura::latest('id')->first();
    expect($ultimaFactura->punto_venta_id)->toBe($this->pv1->id)
        ->and($ultimaFactura->estado)->toBe('VALIDADA');
});

test('generar facturas command rejects invalid punto de venta', function () {
    $this->artisan('siat:generar-facturas', [
        '--cantidad' => 1,
        '--pv' => 999,
    ])
        ->expectsOutputToContain("No se encontró ningún Punto de Venta con código o ID '999'")
        ->assertExitCode(1);
});
