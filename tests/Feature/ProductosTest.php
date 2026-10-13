<?php

use App\Livewire\Inventario\Productos;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('unauthorized users cannot visit the productos index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('inventario.productos'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the productos index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('inventario.productos'))
        ->assertStatus(200)
        ->assertSeeLivewire(Productos::class);
});

use App\Models\Proveedor;

test('can search and filter products by name, composicion, accion_terapeutica and proveedor', function () {
    $user = User::factory()->admin()->create();
    $prov1 = Proveedor::factory()->create(['nombre' => 'Bagó Bolivia', 'razon_social' => 'Laboratorios Bagó S.A.']);
    $prov2 = Proveedor::factory()->create(['nombre' => 'Terbol Farmacéutica', 'razon_social' => 'Laboratorios Terbol S.A.']);

    $prod1 = Producto::factory()->create([
        'nombre' => 'Paracetamol Forte 500mg',
        'principio_activo' => 'Paracetamol',
        'composicion' => 'Cada tableta contiene 500mg de Paracetamol puro',
        'accion_terapeutica' => 'Analgésico y antipirético',
        'codigo' => 'MED-TEST-001',
        'proveedor_id' => $prov1->id,
    ]);

    $prod2 = Producto::factory()->create([
        'nombre' => 'Amoxicilina 500mg',
        'principio_activo' => 'Amoxicilina Trihidrato',
        'composicion' => 'Amoxicilina 500mg por cápsula',
        'accion_terapeutica' => 'Antibiótico bactericida de amplio espectro',
        'codigo' => 'MED-TEST-002',
        'proveedor_id' => $prov2->id,
    ]);

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->set('search', 'Paracetamol')
        ->assertSee('Paracetamol Forte 500mg')
        ->assertDontSee('Amoxicilina 500mg')
        ->set('search', 'bactericida')
        ->assertSee('Amoxicilina 500mg')
        ->assertDontSee('Paracetamol Forte 500mg')
        ->set('search', '')
        ->set('filtroProveedorId', $prov2->id)
        ->assertSee('Amoxicilina 500mg')
        ->assertDontSee('Paracetamol Forte 500mg');
});

test('can create a new product with proveedor, composicion and accion_terapeutica without initial batch', function () {
    $user = User::factory()->admin()->create();
    $prov = Proveedor::factory()->create(['nombre' => 'Droguería INTI', 'razon_social' => 'Droguería INTI S.A.']);

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('nombre', 'Ibuprofeno Forte 600mg')
        ->set('codigo', 'MED-99001')
        ->set('principio_activo', 'Ibuprofeno')
        ->set('proveedor_id', $prov->id)
        ->set('composicion', 'Cada comprimido recubierto contiene Ibuprofeno 600mg')
        ->set('accion_terapeutica', 'Antiinflamatorio no esteroideo y analgésico')
        ->set('presentacion', 'Caja x 30 comprimidos')
        ->set('gramaje', '600 mg')
        ->set('unidad', 'COMPRIMIDO')
        ->set('precio_compra', 12.50)
        ->set('precio_venta', 20.00)
        ->set('minimo_stock', 5)
        ->set('unidades_por_caja', 30)
        ->set('codigo_producto_sin', '83141')
        ->set('unidad_medida_sin', 57)
        ->set('crearLoteInicial', false)
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Ibuprofeno Forte 600mg',
        'codigo' => 'MED-99001',
        'principio_activo' => 'Ibuprofeno',
        'proveedor_id' => $prov->id,
        'composicion' => 'Cada comprimido recubierto contiene Ibuprofeno 600mg',
        'accion_terapeutica' => 'Antiinflamatorio no esteroideo y analgésico',
        'presentacion' => 'Caja x 30 comprimidos',
        'gramaje' => '600 mg',
        'unidad' => 'COMPRIMIDO',
        'precio_compra' => 12.50,
        'precio_venta' => 20.00,
        'usuario_creador_id' => $user->id,
    ]);
});

test('can create a product with initial batch and records kardex movement', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();

    $fechaVencimiento = Carbon::now()->addYear()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('create')
        ->set('nombre', 'Omeprazol 20mg Cápsulas')
        ->set('codigo', 'MED-99002')
        ->set('precio_compra', 8.00)
        ->set('precio_venta', 15.00)
        ->set('minimo_stock', 10)
        ->set('unidades_por_caja', 30)
        ->set('crearLoteInicial', true)
        ->set('numero_lote_inicial', 'LOT-INIT-001')
        ->set('fecha_vencimiento_inicial', $fechaVencimiento)
        ->set('stock_inicial', 50)
        ->set('sucursal_lote_id', $sucursal->id)
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $producto = Producto::where('codigo', 'MED-99002')->first();
    expect($producto)->not->toBeNull();

    $lote = Lote::where('producto_id', $producto->id)->first();
    expect($lote)->not->toBeNull()
        ->and($lote->numero_lote)->toBe('LOT-INIT-001')
        ->and($lote->sucursal_id)->toBe($sucursal->id)
        ->and((float) $lote->stock_actual)->toBe(50.0)
        ->and($lote->fecha_vencimiento->format('Y-m-d'))->toBe($fechaVencimiento);

    $this->assertDatabaseHas('movimientos', [
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'tipo_movimiento' => 'INGRESO_INICIAL',
        'ingreso' => 50,
        'saldo_stock' => 50,
        'numero_lote' => 'LOT-INIT-001',
    ]);
});

test('fails validation when creating product with invalid data or past expiration date', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('create')
        ->set('nombre', '')
        ->set('codigo', '')
        ->set('precio_compra', -5)
        ->set('crearLoteInicial', true)
        ->set('numero_lote_inicial', 'LOT-PAST')
        ->set('fecha_vencimiento_inicial', Carbon::yesterday()->format('Y-m-d'))
        ->set('stock_inicial', 0)
        ->set('sucursal_lote_id', $sucursal->id)
        ->call('save')
        ->assertHasErrors([
            'nombre',
            'codigo',
            'precio_compra',
            'fecha_vencimiento_inicial',
            'stock_inicial',
        ]);
});

test('can edit an existing product', function () {
    $user = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'nombre' => 'Nombre Antiguo',
        'precio_venta' => 10.00,
    ]);

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('edit', $producto->id)
        ->assertSet('showModal', true)
        ->assertSet('nombre', 'Nombre Antiguo')
        ->set('nombre', 'Nombre Actualizado')
        ->set('precio_venta', 18.50)
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Nombre Actualizado',
        'precio_venta' => 18.50,
        'usuario_modificador_id' => $user->id,
    ]);
});

test('can delete a product softly', function () {
    $user = User::factory()->admin()->create();
    $producto = Producto::factory()->create();

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('delete', $producto->id);

    $this->assertSoftDeleted('productos', [
        'id' => $producto->id,
    ]);
});

test('can open and close lotes modal for a product', function () {
    $user = User::factory()->admin()->create();
    $producto = Producto::factory()->create();

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('verLotes', $producto->id)
        ->assertSet('showLotesModal', true)
        ->assertSet('selectedProducto.id', $producto->id)
        ->call('closeLotesModal')
        ->assertSet('showLotesModal', false)
        ->assertSet('selectedProducto', null);
});

test('unauthorized users cannot create, edit or delete products', function () {
    $user = User::factory()->create();
    $producto = Producto::factory()->create();

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('create')
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('edit', $producto->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Productos::class)
        ->call('delete', $producto->id)
        ->assertStatus(403);
});
