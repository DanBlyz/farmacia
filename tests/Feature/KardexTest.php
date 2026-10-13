<?php

use App\Livewire\Inventario\Kardex;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

test('unauthorized users cannot visit the kardex index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('inventario.kardex'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the kardex index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('inventario.kardex'))
        ->assertStatus(200)
        ->assertSeeLivewire(Kardex::class);
});

test('can search and filter kardex movements by batch number, sucursal and type', function () {
    $user = User::factory()->admin()->create();
    $sucursal1 = Sucursal::factory()->create(['nombre' => 'Sucursal Central']);
    $sucursal2 = Sucursal::factory()->create(['nombre' => 'Sucursal Calacoto']);

    $producto1 = Producto::factory()->create(['nombre' => 'Ibuprofeno 400mg']);
    $producto2 = Producto::factory()->create(['nombre' => 'Paracetamol 500mg']);

    $mov1 = Movimiento::create([
        'producto_id' => $producto1->id,
        'sucursal_id' => $sucursal1->id,
        'numero_lote' => 'LOT-KARDEX-001',
        'tipo_movimiento' => 'INGRESO_INICIAL',
        'ingreso' => 50,
        'salida' => 0,
        'saldo_stock' => 50,
        'descripcion' => 'Apertura inicial',
        'usuario_creador_id' => $user->id,
    ]);

    $mov2 = Movimiento::create([
        'producto_id' => $producto2->id,
        'sucursal_id' => $sucursal2->id,
        'numero_lote' => 'LOT-KARDEX-002',
        'tipo_movimiento' => 'BAJA_VENCIMIENTO',
        'ingreso' => 0,
        'salida' => 10,
        'saldo_stock' => 0,
        'descripcion' => 'Baja por caducidad vencida',
        'usuario_creador_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test(Kardex::class)
        ->set('search', '001')
        ->assertSee('LOT-KARDEX-001')
        ->assertDontSee('LOT-KARDEX-002')
        ->set('search', '')
        ->set('filtroSucursalId', $sucursal2->id)
        ->assertSee('LOT-KARDEX-002')
        ->assertDontSee('LOT-KARDEX-001')
        ->set('filtroSucursalId', null)
        ->set('filtroTipoMovimiento', 'BAJA_VENCIMIENTO')
        ->assertSee('LOT-KARDEX-002')
        ->assertDontSee('LOT-KARDEX-001');
});

test('can record manual physical adjustment from kardex modal and updates lot stock', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create();

    $lote = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'stock_actual' => 40,
        'numero_lote' => 'LOT-MANUAL-111',
    ]);

    Livewire::actingAs($user)
        ->test(Kardex::class)
        ->call('abrirModalAjuste')
        ->assertSet('showModal', true)
        ->set('nuevoSucursalId', $sucursal->id)
        ->set('nuevoProductoId', $producto->id)
        ->set('nuevoLoteId', $lote->id)
        ->set('nuevoTipoMovimiento', 'AJUSTE_POSITIVO')
        ->set('nuevaCantidad', 15)
        ->set('nuevoMotivo', 'Conteo físico anual arrojó sobrante')
        ->call('guardarAjusteKardex')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    expect((float) $lote->fresh()->stock_actual)->toBe(55.0);

    $this->assertDatabaseHas('movimientos', [
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'AJUSTE_POSITIVO',
        'ingreso' => 15,
        'saldo_stock' => 55,
        'numero_lote' => 'LOT-MANUAL-111',
    ]);
});

test('fails validation when recording manual adjustment with missing fields or excess subtraction', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create();
    $lote = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'stock_actual' => 5,
    ]);

    Livewire::actingAs($user)
        ->test(Kardex::class)
        ->call('abrirModalAjuste')
        ->set('nuevoSucursalId', null)
        ->set('nuevoProductoId', null)
        ->set('nuevoLoteId', null)
        ->set('nuevaCantidad', 0)
        ->set('nuevoMotivo', '')
        ->call('guardarAjusteKardex')
        ->assertHasErrors(['nuevoSucursalId', 'nuevoProductoId', 'nuevoLoteId', 'nuevaCantidad', 'nuevoMotivo']);

    // Intentar restar más que el stock del lote
    Livewire::actingAs($user)
        ->test(Kardex::class)
        ->call('abrirModalAjuste')
        ->set('nuevoSucursalId', $sucursal->id)
        ->set('nuevoProductoId', $producto->id)
        ->set('nuevoLoteId', $lote->id)
        ->set('nuevoTipoMovimiento', 'AJUSTE_NEGATIVO')
        ->set('nuevaCantidad', 100)
        ->set('nuevoMotivo', 'Intento de merma imposible')
        ->call('guardarAjusteKardex')
        ->assertHasErrors(['nuevaCantidad']);
});

test('unauthorized users cannot record adjustments in kardex', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Kardex::class)
        ->call('abrirModalAjuste')
        ->assertStatus(403);
});

test('can search and select product dynamically in kardex adjustment modal', function () {
    $user = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'nombre' => 'Clonazepam 2mg Gotas',
        'codigo' => 'MED-CLO-002',
    ]);

    Livewire::actingAs($user)
        ->test(Kardex::class)
        ->call('abrirModalAjuste')
        ->set('productoSearch', 'Clonazepam')
        ->assertSee('Clonazepam 2mg Gotas')
        ->call('seleccionarProducto', $producto->id)
        ->assertSet('nuevoProductoId', $producto->id)
        ->assertSet('productoSearch', '')
        ->assertSee('MED-CLO-002')
        ->call('limpiarProducto')
        ->assertSet('nuevoProductoId', null);
});
