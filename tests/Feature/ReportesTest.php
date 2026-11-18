<?php

use App\Livewire\Reportes\ReportesIndex;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->sucursal = Sucursal::firstOrCreate(
        ['codigo_sucursal' => 0],
        ['nombre' => 'Casa Matriz Principal', 'direccion' => 'Av. Central 123']
    );

    // Permisos de reportes
    Permiso::firstOrCreate(['slug' => 'reportes.inventario.ver'], ['nombre' => 'Ver Reporte Inventario', 'modulo' => 'reportes']);
    Permiso::firstOrCreate(['slug' => 'reportes.utilidades.ver'], ['nombre' => 'Ver Reporte Utilidades', 'modulo' => 'reportes']);
    Permiso::firstOrCreate(['slug' => 'reportes.cajas.ver'], ['nombre' => 'Ver Reporte Cajas', 'modulo' => 'reportes']);
    Permiso::firstOrCreate(['slug' => 'reportes.clientes.ver'], ['nombre' => 'Ver Reporte Clientes', 'modulo' => 'reportes']);
});

test('unauthenticated users are redirected to login from reportes', function () {
    $this->get(route('reportes.index'))
        ->assertRedirect(route('login'));
});

test('authenticated admin user can access reportes and see component', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('reportes.index'))
        ->assertStatus(200)
        ->assertSeeLivewire(ReportesIndex::class);
});

test('livewire reportes component renders tabs and allows switching', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(ReportesIndex::class)
        ->assertSet('tab', 'inventario')
        ->assertSee('Inventario y Kardex')
        ->call('cambiarTab', 'utilidades')
        ->assertSet('tab', 'utilidades')
        ->assertSee('Utilidades y Ganancias')
        ->call('cambiarTab', 'cajas')
        ->assertSet('tab', 'cajas')
        ->assertSee('Control de Cajas')
        ->call('cambiarTab', 'clientes')
        ->assertSet('tab', 'clientes')
        ->assertSee('Clientes Frecuentes');
});

test('reporte de inventario calculates stock and valuations correctly', function () {
    $admin = User::factory()->admin()->create();

    $producto = Producto::create([
        'nombre' => 'Paracetamol 500mg Test',
        'codigo' => 'PROD-REP-01',
        'precio_compra' => 5.00,
        'precio_venta' => 8.00,
        'estado' => 'ACTIVO',
    ]);

    Movimiento::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo' => 'INGRESO_COMPRA',
        'ingreso' => 100,
        'salida' => 0,
        'saldo' => 100,
        'fecha' => now()->toDateString(),
        'glosa' => 'Compra inicial',
    ]);

    Movimiento::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo' => 'SALIDA_VENTA',
        'ingreso' => 0,
        'salida' => 20,
        'saldo' => 80,
        'fecha' => now()->toDateString(),
        'glosa' => 'Venta mostrador',
    ]);

    Livewire::actingAs($admin)
        ->test(ReportesIndex::class, ['tab' => 'inventario'])
        ->set('filtroProductoId', $producto->id)
        ->assertSee('Paracetamol 500mg Test')
        ->assertSee('+100')
        ->assertSee('-20')
        ->assertSee('80');
});

test('pdf exports stream valid pdf responses for all four reports', function () {
    $admin = User::factory()->admin()->create();

    $cliente = Cliente::create([
        'razon_social' => 'Farmacia Salud SRL',
        'nit' => '1020304050',
        'complemento' => null,
    ]);

    $producto = Producto::create([
        'nombre' => 'Amoxicilina 500mg PDF',
        'codigo' => 'PROD-PDF-01',
        'precio_compra' => 10.00,
        'precio_venta' => 15.00,
        'estado' => 'ACTIVO',
    ]);

    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'numero_lote' => 'LOT-REP-01',
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'stock_actual' => 50,
        'precio_compra' => 10.00,
    ]);

    $caja = Caja::create([
        'usuario_id' => $admin->id,
        'sucursal_id' => $this->sucursal->id,
        'monto_apertura' => 200.00,
        'fecha_apertura' => now(),
        'estado' => 'ABIERTA',
    ]);

    $factura = Factura::create([
        'numero_factura' => 9991,
        'cuf' => 'CUF-TEST-9991',
        'cufd' => 'CUFD-TEST',
        'fecha' => now(),
        'sucursal_id' => $this->sucursal->id,
        'usuario_creador_id' => $admin->id,
        'caja_id' => $caja->id,
        'cliente_id' => $cliente->id,
        'nit' => $cliente->nit,
        'razon_social' => $cliente->razon_social,
        'total' => 30.00,
        'subtotal' => 30.00,
        'monto_total_sujeto_iva' => 30.00,
        'estado' => 'VALIDADA',
    ]);

    Detalle::create([
        'factura_id' => $factura->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'cantidad' => 2,
        'precio' => 15.00,
        'total' => 30.00,
    ]);

    Pago::create([
        'factura_id' => $factura->id,
        'caja_id' => $caja->id,
        'tipo_pago' => 'EFECTIVO',
        'monto' => 50.00,
        'cambio' => 20.00,
    ]);

    // 1. PDF Inventario
    $resInv = $this->actingAs($admin)->get(route('reportes.inventario.pdf'));
    $resInv->assertStatus(200);
    expect($resInv->headers->get('content-type'))->toContain('application/pdf');

    // 2. PDF Utilidades
    $resUtil = $this->actingAs($admin)->get(route('reportes.utilidades.pdf'));
    $resUtil->assertStatus(200);
    expect($resUtil->headers->get('content-type'))->toContain('application/pdf');

    // 3. PDF Cajas
    $resCajas = $this->actingAs($admin)->get(route('reportes.cajas.pdf'));
    $resCajas->assertStatus(200);
    expect($resCajas->headers->get('content-type'))->toContain('application/pdf');

    // 4. PDF Clientes
    $resCli = $this->actingAs($admin)->get(route('reportes.clientes.pdf'));
    $resCli->assertStatus(200);
    expect($resCli->headers->get('content-type'))->toContain('application/pdf');
});
