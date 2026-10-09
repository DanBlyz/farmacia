<?php

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;

test('it dispatches the lot with the earliest expiration date under FEFO', function () {
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create();

    // Lote que vence en 12 meses
    $loteLejano = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-LEJANO',
        'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
        'stock_actual' => 50,
        'estado' => 'ACTIVO',
    ]);

    // Lote que vence en 3 meses (debe salir primero según FEFO)
    $loteProximo = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-PRIMERO',
        'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
        'stock_actual' => 20,
        'estado' => 'ACTIVO',
    ]);

    // Lote que vence en 6 meses
    $loteMedio = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-MEDIO',
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
        'stock_actual' => 30,
        'estado' => 'ACTIVO',
    ]);

    $loteCandidato = $producto->loteFefoDisponible($sucursal->id);

    expect($loteCandidato)->not->toBeNull();
    expect($loteCandidato->id)->toBe($loteProximo->id);
    expect($loteCandidato->numero_lote)->toBe('LOTE-PRIMERO');
});

test('it excludes expired lots from FEFO dispatch and classifies semaforo as rojo', function () {
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create();

    // Lote ya vencido
    $loteVencido = Lote::factory()->vencido(10)->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-CADUCADO',
        'stock_actual' => 15,
    ]);

    // Lote vigente
    $loteVigente = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-VALIDO',
        'fecha_vencimiento' => now()->addMonths(5)->toDateString(),
        'stock_actual' => 40,
        'estado' => 'ACTIVO',
    ]);

    expect($loteVencido->esVencido())->toBeTrue();
    expect($loteVencido->estado_semaforo)->toBe('rojo');

    // FEFO debe ignorar el vencido y seleccionar el lote vigente
    $loteCandidato = $producto->loteFefoDisponible($sucursal->id);
    expect($loteCandidato)->not->toBeNull();
    expect($loteCandidato->id)->toBe($loteVigente->id);
    expect($loteCandidato->numero_lote)->toBe('LOTE-VALIDO');
});

test('it classifies near-expiry lots as semaforo naranja and optimal lots as verde', function () {
    $loteCritico = Lote::factory()->proximoAVencer(25)->create();
    $loteOptimo = Lote::factory()->create([
        'fecha_vencimiento' => now()->addMonths(10)->toDateString(),
    ]);

    expect($loteCritico->esVencido())->toBeFalse();
    expect($loteCritico->esProximoAVencer(60))->toBeTrue();
    expect($loteCritico->estado_semaforo)->toBe('naranja');

    expect($loteOptimo->esVencido())->toBeFalse();
    expect($loteOptimo->esProximoAVencer(60))->toBeFalse();
    expect($loteOptimo->estado_semaforo)->toBe('verde');
});

test('it calculates decentralized stock per sucursal accurately', function () {
    $sucursalA = Sucursal::factory()->create(['nombre' => 'Sucursal Central']);
    $sucursalB = Sucursal::factory()->create(['nombre' => 'Sucursal Calacoto']);
    $producto = Producto::factory()->create();

    // Stock en Sucursal A: 20 + 30 = 50
    Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursalA->id,
        'stock_actual' => 20,
        'fecha_vencimiento' => now()->addMonths(8)->toDateString(),
    ]);
    Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursalA->id,
        'stock_actual' => 30,
        'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
    ]);

    // Stock en Sucursal B: 15
    Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursalB->id,
        'stock_actual' => 15,
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    // Lote vencido en Sucursal A (no debe sumarse al stock disponible)
    Lote::factory()->vencido(5)->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursalA->id,
        'stock_actual' => 10,
    ]);

    expect($producto->stockEnSucursal($sucursalA->id))->toBe(50.0);
    expect($producto->stockEnSucursal($sucursalB->id))->toBe(15.0);
    expect($producto->stockTotal())->toBe(65.0);
});

test('it decrements batch stock correctly and marks lot as AGOTADO when reaching zero', function () {
    $lote = Lote::factory()->create([
        'stock_actual' => 10,
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
        'estado' => 'ACTIVO',
    ]);

    $lote->descontarStock(4);
    expect((float) $lote->stock_actual)->toBe(6.0);
    expect($lote->estado)->toBe('ACTIVO');

    $lote->descontarStock(6);
    expect((float) $lote->stock_actual)->toBe(0.0);
    expect($lote->estado)->toBe('AGOTADO');
});

test('it prevents discounting from an expired lot and throws a runtime exception', function () {
    $lote = Lote::factory()->vencido(5)->create([
        'stock_actual' => 20,
    ]);

    expect(fn () => $lote->descontarStock(5))
        ->toThrow(RuntimeException::class);
});

test('it supports soft deletes and auditable tracking on lotes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lote = Lote::factory()->create([
        'numero_lote' => 'LOTE-AUDIT',
        'usuario_creador_id' => $user->id,
    ]);

    $this->assertDatabaseHas('lotes', [
        'id' => $lote->id,
        'numero_lote' => 'LOTE-AUDIT',
        'usuario_creador_id' => $user->id,
    ]);

    $lote->delete();
    $this->assertSoftDeleted('lotes', ['id' => $lote->id]);
});
