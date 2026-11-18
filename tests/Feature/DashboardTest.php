<?php

use App\Livewire\Dashboard;
use App\Models\Cliente;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('unauthenticated users are redirected to login from dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit dashboard and see livewire component', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(200)
        ->assertSeeLivewire(Dashboard::class);
});

test('dashboard displays real metrics for products, sales, lots and clients', function () {
    $user = User::factory()->admin()->create();

    $sucursal = Sucursal::firstOrCreate(
        ['codigo_sucursal' => 0],
        ['nombre' => 'Casa Matriz Sopocachi', 'direccion' => 'Capitán Ravelo']
    );

    $producto = Producto::create([
        'nombre' => 'Ibuprofeno 400mg',
        'codigo' => 'MED-TEST-01',
        'minimo_stock' => 15.00,
        'precio_venta' => 12.00,
        'estado' => 'ACTIVO',
    ]);

    // Lote vigente con stock bajo (5 < 15)
    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOT-DASH-01',
        'fecha_vencimiento' => now()->addDays(20)->toDateString(),
        'stock_actual' => 5.00,
        'stock_minimo_alerta' => 15.00,
        'estado' => 'ACTIVO',
    ]);

    $cliente = Cliente::firstOrCreate(
        ['numero_documento' => '1234567'],
        ['razon_social' => 'Cliente Dashboard', 'codigo_tipo_documento_identidad' => 1]
    );

    // Factura de hoy
    $factura = Factura::create([
        'sucursal_id' => $sucursal->id,
        'cliente_id' => $cliente->id,
        'numero_factura' => 9999,
        'fecha' => Carbon::now(),
        'total' => 24.00,
        'monto_total_subjeto_iva' => 24.00,
        'estado' => 'VALIDADA',
        'nit' => '1234567',
        'razon_social' => 'Cliente Dashboard',
        'codigo_metodo_pago' => 1,
    ]);

    Detalle::create([
        'factura_id' => $factura->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'sucursal_id' => $sucursal->id,
        'nombre_producto' => 'Ibuprofeno 400mg',
        'numero_lote' => 'LOT-DASH-01',
        'fecha_vencimiento' => $lote->fecha_vencimiento,
        'precio' => 12.00,
        'cantidad' => 2.00,
        'total' => 24.00,
        'fecha' => Carbon::now(),
        'estado' => 'ACTIVO',
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Ibuprofeno 400mg')
        ->assertSee('MED-TEST-01')
        ->assertSee('Productos con Stock por Debajo del Mínimo')
        ->assertSee('Productos Más Vendidos')
        ->assertSee('2 uds.');
});

test('dashboard can filter by sucursal and search terms', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('searchStockBajo', 'InexistenteXYZ')
        ->assertSee('¡Inventario en Estado Óptimo!')
        ->call('limpiarFiltrosStockBajo')
        ->assertSet('searchStockBajo', '')
        ->set('searchMasVendidos', 'InexistenteABC')
        ->assertSee('Sin Ventas Registradas')
        ->call('limpiarFiltrosMasVendidos')
        ->assertSet('searchMasVendidos', '')
        ->assertSet('filtroPeriodo', 'todos');
});
