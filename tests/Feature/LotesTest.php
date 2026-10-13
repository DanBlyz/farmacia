<?php

use App\Livewire\Inventario\Lotes;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('unauthorized users cannot visit the lotes index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('inventario.lotes'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the lotes index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('inventario.lotes'))
        ->assertStatus(200)
        ->assertSeeLivewire(Lotes::class);
});

test('can search and filter lotes by batch number, sucursal and semaforo', function () {
    $user = User::factory()->admin()->create();
    $sucursal1 = Sucursal::factory()->create(['nombre' => 'Sucursal Norte']);
    $sucursal2 = Sucursal::factory()->create(['nombre' => 'Sucursal Sur']);

    $producto = Producto::factory()->create(['nombre' => 'Amoxicilina 500mg']);

    $lote1 = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal1->id,
        'numero_lote' => 'LOT-ALFA-100',
        'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
    ]);

    $lote2 = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal2->id,
        'numero_lote' => 'LOT-BETA-200',
        'fecha_vencimiento' => Carbon::now()->subDays(10)->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->set('search', 'ALFA')
        ->assertSee('LOT-ALFA-100')
        ->assertDontSee('LOT-BETA-200')
        ->set('search', '')
        ->set('filtroSucursalId', $sucursal2->id)
        ->assertSee('LOT-BETA-200')
        ->assertDontSee('LOT-ALFA-100')
        ->set('filtroSucursalId', null)
        ->set('filtroSemaforo', 'vencido')
        ->assertSee('LOT-BETA-200')
        ->assertDontSee('LOT-ALFA-100');
});

test('can create a new batch and automatically records initial movement in kardex', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create();

    $fechaVencimiento = Carbon::now()->addYear()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('producto_id', $producto->id)
        ->set('sucursal_id', $sucursal->id)
        ->set('numero_lote', 'LOT-NEW-777')
        ->set('fecha_vencimiento', $fechaVencimiento)
        ->set('stock_actual', 100)
        ->set('stock_minimo_alerta', 15)
        ->set('precio_compra', 12.00)
        ->set('precio_venta', 22.50)
        ->set('ubicacion_estante', 'Estante C-4')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $lote = Lote::where('numero_lote', 'LOT-NEW-777')->first();
    expect($lote)->not->toBeNull()
        ->and((float) $lote->stock_actual)->toBe(100.0)
        ->and($lote->sucursal_id)->toBe($sucursal->id)
        ->and($lote->fecha_vencimiento->format('Y-m-d'))->toBe($fechaVencimiento);

    $this->assertDatabaseHas('movimientos', [
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'INGRESO_INICIAL',
        'ingreso' => 100,
        'saldo_stock' => 100,
    ]);
});

test('fails validation when creating batch with past date or empty required fields', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('create')
        ->set('producto_id', null)
        ->set('numero_lote', '')
        ->set('fecha_vencimiento', Carbon::yesterday()->format('Y-m-d'))
        ->set('stock_actual', 0)
        ->call('save')
        ->assertHasErrors(['producto_id', 'numero_lote', 'fecha_vencimiento', 'stock_actual']);
});

test('can adjust batch stock positively and negatively via modal and updates kardex', function () {
    $user = User::factory()->admin()->create();
    $lote = Lote::factory()->create([
        'stock_actual' => 50,
    ]);

    // 1. Ajuste Positivo
    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('abrirAjuste', $lote->id)
        ->assertSet('showAjusteModal', true)
        ->set('tipoAjuste', 'AJUSTE_POSITIVO')
        ->set('cantidadAjuste', 20)
        ->set('motivoAjuste', 'Sobrante detectado en auditoría física')
        ->call('guardarAjuste')
        ->assertSet('showAjusteModal', false)
        ->assertHasNoErrors();

    expect((float) $lote->fresh()->stock_actual)->toBe(70.0);

    $this->assertDatabaseHas('movimientos', [
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'AJUSTE_POSITIVO',
        'ingreso' => 20,
        'saldo_stock' => 70,
    ]);

    // 2. Ajuste Negativo
    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('abrirAjuste', $lote->id)
        ->set('tipoAjuste', 'AJUSTE_NEGATIVO')
        ->set('cantidadAjuste', 15)
        ->set('motivoAjuste', 'Frascos rotos por caída de anaquel')
        ->call('guardarAjuste')
        ->assertSet('showAjusteModal', false)
        ->assertHasNoErrors();

    expect((float) $lote->fresh()->stock_actual)->toBe(55.0);

    $this->assertDatabaseHas('movimientos', [
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'AJUSTE_NEGATIVO',
        'salida' => 15,
        'saldo_stock' => 55,
    ]);
});

test('cannot decrement more stock than available in batch', function () {
    $user = User::factory()->admin()->create();
    $lote = Lote::factory()->create([
        'stock_actual' => 10,
    ]);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('abrirAjuste', $lote->id)
        ->set('tipoAjuste', 'AJUSTE_NEGATIVO')
        ->set('cantidadAjuste', 50)
        ->set('motivoAjuste', 'Intento de merma excesiva')
        ->call('guardarAjuste')
        ->assertHasErrors(['cantidadAjuste']);

    expect((float) $lote->fresh()->stock_actual)->toBe(10.0);
});

test('can toggle block and unblock batch for sanitary alert or quarantine', function () {
    $user = User::factory()->admin()->create();
    $lote = Lote::factory()->create([
        'estado' => 'ACTIVO',
    ]);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('toggleBloqueo', $lote->id);

    expect($lote->fresh()->estado)->toBe('BLOQUEADO');

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('toggleBloqueo', $lote->id);

    expect($lote->fresh()->estado)->toBe('ACTIVO');
});

test('can delete a batch softly', function () {
    $user = User::factory()->admin()->create();
    $lote = Lote::factory()->create();

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('delete', $lote->id);

    $this->assertSoftDeleted('lotes', [
        'id' => $lote->id,
    ]);
});

test('unauthorized users cannot create, edit or delete lotes', function () {
    $user = User::factory()->create();
    $lote = Lote::factory()->create();

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('create')
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('edit', $lote->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('abrirAjuste', $lote->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('delete', $lote->id)
        ->assertStatus(403);
});

test('can search and select product dynamically in lotes modal', function () {
    $user = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'nombre' => 'Azitromicina 500mg Polvo',
        'codigo' => 'MED-AZI-001',
    ]);

    Livewire::actingAs($user)
        ->test(Lotes::class)
        ->call('create')
        ->set('productoSearch', 'Azitromicina')
        ->assertSee('Azitromicina 500mg Polvo')
        ->call('seleccionarProducto', $producto->id)
        ->assertSet('producto_id', $producto->id)
        ->assertSet('productoSearch', '')
        ->assertSee('MED-AZI-001')
        ->call('limpiarProducto')
        ->assertSet('producto_id', null);
});
