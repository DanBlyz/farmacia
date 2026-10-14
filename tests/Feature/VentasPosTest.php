<?php

use App\Livewire\Ventas\HistorialVentas;
use App\Livewire\Ventas\Pos;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\MotivoAnulacion;
use App\Models\Producto;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\SiatFacturacionService;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    Cliente::firstOrCreate(['id' => 1], [
        'nombres' => 'Sin Nombre',
        'razon_social' => 'S/N',
        'nit' => '99001',
        'cedula' => '99001',
        'estado' => 'ACTIVO',
    ]);

    MotivoAnulacion::firstOrCreate(['id' => 1], [
        'codigo_clasificador' => 1,
        'descripcion' => 'FACTURA MAL EMITIDA',
        'estado' => 'ACTIVO',
    ]);
});

test('unauthorized users cannot visit the pos page or historial page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('ventas.pos'))
        ->assertStatus(403);

    $this->actingAs($user)
        ->get(route('ventas.historial'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the pos page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('ventas.pos'))
        ->assertStatus(200)
        ->assertSeeLivewire(Pos::class);
});

test('pos defaults to client ID 1 (S/N) and allows fast registration of new client with separated CI and NIT fields', function () {
    $user = User::factory()->admin()->create();

    // 1. Registro con Cédula de Identidad y Complemento
    Livewire::actingAs($user)
        ->test(Pos::class)
        ->assertSet('clienteId', 1)
        ->assertSet('clienteRazonSocial', 'S/N')
        ->assertSet('clienteDocumento', '99001')
        ->call('abrirModalNuevoCliente')
        ->assertSet('showNuevoClienteModal', true)
        ->set('nuevoClienteNombres', 'María Elena')
        ->set('nuevoClienteApellidos', 'Vargas Quispe')
        ->set('nuevoClienteCedula', '6892015')
        ->set('nuevoClienteComplemento', '1B')
        ->set('nuevoClienteRazonSocial', 'VARGAS MARIA')
        ->set('nuevoClienteCelular', '76512345')
        ->call('guardarNuevoCliente')
        ->assertSet('showNuevoClienteModal', false)
        ->assertSet('clienteDocumento', '6892015')
        ->assertSet('clienteRazonSocial', 'VARGAS MARIA');

    $this->assertDatabaseHas('clientes', [
        'cedula' => '6892015',
        'complemento' => '1B',
        'nit' => null,
        'razon_social' => 'VARGAS MARIA',
    ]);

    // 2. Registro con NIT exclusivo
    Livewire::actingAs($user)
        ->test(Pos::class)
        ->call('abrirModalNuevoCliente')
        ->set('nuevoClienteNombres', 'Distribuidora Farmacéutica')
        ->set('nuevoClienteNit', '1028475027')
        ->set('nuevoClienteRazonSocial', 'DISTRIBUIDORA FARMACEUTICA S.R.L.')
        ->call('guardarNuevoCliente')
        ->assertSet('showNuevoClienteModal', false)
        ->assertSet('clienteDocumento', '1028475027')
        ->assertSet('clienteRazonSocial', 'DISTRIBUIDORA FARMACEUTICA S.R.L.');

    $this->assertDatabaseHas('clientes', [
        'cedula' => null,
        'nit' => '1028475027',
        'razon_social' => 'DISTRIBUIDORA FARMACEUTICA S.R.L.',
    ]);
});

test('can process a sale with multiple payment methods, discounts FEFO batch stock and creates kardex movement', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create(['codigo_sucursal' => 0]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Caja Central 1',
        'codigo' => 0,
        'estado' => 'ACTIVO',
    ]);

    // Crear CUFD activo para que la facturación lo tome
    $cufd = Cufd::create([
        'usuario_creador_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'codigo_ambiente' => 2,
        'codigo' => 'B0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0',
        'codigo_control' => 'CONTROL123456',
        'direccion' => 'Av. Principal Central Farma',
        'fecha_vigencia' => now()->addHours(24),
    ]);

    // Caja abierta
    $caja = Caja::factory()->create([
        'usuario_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'monto_apertura' => 200.00,
        'saldo' => 200.00,
        'estado' => 'ABIERTA',
    ]);

    // Producto y Lote
    $producto = Producto::factory()->create([
        'nombre' => 'Ibuprofeno 400mg Comprimidos',
        'precio_venta' => 20.00,
    ]);

    $lote = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOT-IBU-POS-01',
        'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
        'stock_actual' => 50,
        'precio_venta' => 20.00,
        'estado' => 'ACTIVO',
    ]);

    Livewire::actingAs($user)
        ->test(Pos::class)
        ->call('seleccionarProducto', $producto->id)
        ->assertSet('selectedProductoId', $producto->id)
        ->assertSet('selectedLoteId', 'AUTO_FEFO')
        ->set('cantidadAgregar', 3) // 3 * 20 = 60 Bs
        ->set('descuentoItemAgregar', 5) // Descuento 5 Bs = 55 Bs
        ->call('agregarAlCarrito')
        ->set('descuentoGeneral', 5) // Descuento general 5 Bs = Total a pagar 50 Bs
        ->set('montoEfectivo', 30.00) // Paga 30 Bs en efectivo
        ->set('montoQr', 20.00) // Paga 20 Bs en QR = Total 50 Bs
        ->call('procesarVenta')
        ->assertHasNoErrors()
        ->assertSet('showTicketModal', true);

    // Verificar que se descontó el stock del lote físico
    expect((float) $lote->fresh()->stock_actual)->toBe(47.0);

    // Verificar creación de la factura
    $factura = Factura::where('sucursal_id', $sucursal->id)->latest('id')->first();
    expect($factura)->not->toBeNull()
        ->and((float) $factura->total)->toBe(50.00)
        ->and((float) $factura->descuento_adicional)->toBe(5.00)
        ->and($factura->cuf)->not->toBeNull()
        ->and($factura->cliente_id)->toBe(1)
        ->and($factura->productos_xml)->toContain('<codigoTipoDocumentoIdentidad>5</codigoTipoDocumentoIdentidad>')
        ->and($factura->productos_xml)->toContain('<numeroDocumento>99001</numeroDocumento>')
        ->and($factura->productos_xml)->toContain('<codigoExcepcion>1</codigoExcepcion>');

    // Verificar que se crearon los detalles
    $this->assertDatabaseHas('detalles', [
        'factura_id' => $factura->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'numero_lote' => 'LOT-IBU-POS-01',
        'cantidad' => 3,
    ]);

    // Verificar movimiento en Kardex
    $this->assertDatabaseHas('movimientos', [
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'VENTA',
        'salida' => 3,
        'saldo_stock' => 47,
    ]);

    // Verificar acumuladores de la caja
    $cajaActualizada = $caja->fresh();
    expect((float) $cajaActualizada->total_venta)->toBe(50.00)
        ->and((float) $cajaActualizada->venta_contado)->toBe(30.00)
        ->and((float) $cajaActualizada->total_qr)->toBe(20.00)
        ->and((float) $cajaActualizada->saldo)->toBe(230.00); // 200 apertura + 30 efectivo
});

test('can cancel (anular) an invoice from historial, returns stock to physical batch and updates kardex', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create();

    $lote = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'stock_actual' => 10,
        'numero_lote' => 'LOT-ANULAR-01',
    ]);

    $factura = Factura::create([
        'usuario_creador_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'cliente_id' => 1,
        'fecha' => now(),
        'nit' => '99001',
        'razon_social' => 'S/N',
        'numero_factura' => '9991',
        'total' => 40.00,
        'monto_total_subjeto_iva' => 40.00,
        'cuf' => 'ABC123CUFTEST',
        'estado' => 'VALIDADA',
    ]);

    Detalle::create([
        'usuario_creador_id' => $user->id,
        'factura_id' => $factura->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'sucursal_id' => $sucursal->id,
        'nombre_producto' => $producto->nombre,
        'numero_lote' => $lote->numero_lote,
        'fecha_vencimiento' => $lote->fecha_vencimiento,
        'precio' => 20.00,
        'cantidad' => 2,
        'total' => 40.00,
        'importe' => 40.00,
        'fecha' => now(),
        'estado' => 'ACTIVO',
    ]);

    Livewire::actingAs($user)
        ->test(HistorialVentas::class)
        ->call('abrirModalAnulacion', $factura->id)
        ->assertSet('showAnulacionModal', true)
        ->set('motivoAnulacionId', 1)
        ->set('motivoAnulacionTexto', 'Error en cobro digitado')
        ->call('confirmarAnulacion')
        ->assertSet('showAnulacionModal', false)
        ->assertHasNoErrors();

    expect($factura->fresh()->estado)->toBe('ANULADA')
        ->and((float) $lote->fresh()->stock_actual)->toBe(12.0); // 10 + 2 devueltos

    $this->assertDatabaseHas('movimientos', [
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'ANULACION_VENTA',
        'ingreso' => 2,
        'saldo_stock' => 12,
    ]);
});

test('unauthorized users cannot access the invoice ticket pdf print route', function () {
    $user = User::factory()->create();
    $factura = Factura::factory()->create();

    $this->actingAs($user)
        ->get(route('ventas.factura.imprimir', $factura->id))
        ->assertStatus(403);
});

test('authenticated users with permission can generate and stream thermal ticket pdf with dompdf', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create();
    $producto = Producto::factory()->create(['nombre' => 'Ibuprofeno 400mg']);
    $factura = Factura::factory()->create([
        'sucursal_id' => $sucursal->id,
        'numero_factura' => '8882',
        'cuf' => '1234567890ABCDEF1234567890ABCDEF',
        'total' => 50.00,
        'monto_total_subjeto_iva' => 50.00,
    ]);

    Detalle::create([
        'usuario_creador_id' => $user->id,
        'factura_id' => $factura->id,
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'nombre_producto' => $producto->nombre,
        'numero_lote' => 'LOT-2026-001',
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio' => 25.00,
        'cantidad' => 2,
        'total' => 50.00,
        'importe' => 50.00,
        'fecha' => now(),
        'estado' => 'ACTIVO',
    ]);

    $response = $this->actingAs($user)
        ->get(route('ventas.factura.imprimir', $factura->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->getContent())->toContain('%PDF');
});

test('auto fefo dispenses across multiple lots according to expiration dates and handles decimal payments with precision', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create(['codigo_sucursal' => 0]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Caja Central 1',
        'codigo' => 0,
        'estado' => 'ACTIVO',
    ]);

    Cufd::create([
        'usuario_creador_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'codigo_ambiente' => 2,
        'codigo' => 'B0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0',
        'codigo_control' => 'CONTROL123456',
        'direccion' => 'Av. Principal Central Farma',
        'fecha_vigencia' => now()->addHours(24),
    ]);

    Caja::factory()->create([
        'usuario_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'monto_apertura' => 100.00,
        'saldo' => 100.00,
        'estado' => 'ABIERTA',
    ]);

    $producto = Producto::factory()->create([
        'nombre' => 'Paracetamol 500mg Jarabe',
        'precio_venta' => 10.00,
    ]);

    // Lote 1: vence en 1 mes (2 unidades)
    $lote1 = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-MES-1',
        'fecha_vencimiento' => Carbon::now()->addMonth()->toDateString(),
        'stock_actual' => 2,
        'precio_venta' => 10.00,
        'estado' => 'ACTIVO',
    ]);

    // Lote 2: vence en 2 meses (3 unidades)
    $lote2 = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-MES-2',
        'fecha_vencimiento' => Carbon::now()->addMonths(2)->toDateString(),
        'stock_actual' => 3,
        'precio_venta' => 10.00,
        'estado' => 'ACTIVO',
    ]);

    // Lote 3: vence en 5 meses (5 unidades)
    $lote3 = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-MES-5',
        'fecha_vencimiento' => Carbon::now()->addMonths(5)->toDateString(),
        'stock_actual' => 5,
        'precio_venta' => 10.00,
        'estado' => 'ACTIVO',
    ]);

    // Cliente pide 5 unidades en total. Debe consumir 2 de lote1 y 3 de lote2. Lote3 queda intacto.
    Livewire::actingAs($user)
        ->test(Pos::class)
        ->call('seleccionarProducto', $producto->id)
        ->assertSet('selectedLoteId', 'AUTO_FEFO')
        ->set('cantidadAgregar', 5)
        ->call('agregarAlCarrito')
        // Total = 5 * 10 = 50.00 Bs. Supongamos un descuento general para tener decimales: 10.60 Bs -> total a pagar 39.40 Bs
        ->set('descuentoGeneral', 10.60)
        ->assertSet('montoEfectivo', '39.40')
        // Simular pago mixto: 30 en efectivo y saldo restante en QR
        ->set('montoEfectivo', '30.00')
        ->call('ajustarSaldoMetodo', 'qr')
        ->assertSet('montoQr', '9.40')
        ->call('procesarVenta')
        ->assertHasNoErrors()
        ->assertSet('showTicketModal', true);

    // Verificar consumos en los lotes
    expect((float) $lote1->fresh()->stock_actual)->toBe(0.0)
        ->and($lote1->fresh()->estado)->toBe('AGOTADO')
        ->and((float) $lote2->fresh()->stock_actual)->toBe(0.0)
        ->and($lote2->fresh()->estado)->toBe('AGOTADO')
        ->and((float) $lote3->fresh()->stock_actual)->toBe(5.0);
});

test('selling price strictly prioritizes updated product price over lote price', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create(['codigo_sucursal' => 0]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Caja Central 1',
        'codigo' => 0,
        'estado' => 'ACTIVO',
    ]);

    Cufd::create([
        'usuario_creador_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'codigo_ambiente' => 2,
        'codigo' => 'B0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0',
        'codigo_control' => 'CONTROL123456',
        'direccion' => 'Av. Principal Central Farma',
        'fecha_vigencia' => now()->addHours(24),
    ]);

    Caja::factory()->create([
        'usuario_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'monto_apertura' => 100.00,
        'saldo' => 100.00,
        'estado' => 'ABIERTA',
    ]);

    // Producto actualizado con precio nuevo de 35.00 Bs
    $producto = Producto::factory()->create([
        'nombre' => 'Amoxicilina 500mg Cápsulas',
        'precio_venta' => 35.00,
    ]);

    // Lote antiguo que se registró previamente con precio de 20.00 Bs
    $loteAntiguo = Lote::factory()->create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOTE-VIEJO-01',
        'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
        'stock_actual' => 10,
        'precio_compra' => 12.00,
        'precio_venta' => 20.00, // Precio desactualizado del lote
        'estado' => 'ACTIVO',
    ]);

    // Al agregar 2 unidades en el POS, el precio debe ser 35.00 Bs (total 70.00 Bs), NO 20.00 Bs
    $test = Livewire::actingAs($user)
        ->test(Pos::class)
        ->call('seleccionarProducto', $producto->id)
        ->set('cantidadAgregar', 2)
        ->call('agregarAlCarrito');

    $carrito = $test->get('carrito');
    expect($carrito[0]['precio'])->toBe(35.00)
        ->and($carrito[0]['total'])->toBe(70.00);

    // Procesar venta y verificar que la factura y el detalle se generaron a 35.00 Bs
    $test->set('montoEfectivo', '70.00')
        ->call('procesarVenta')
        ->assertHasNoErrors();

    $factura = Factura::where('sucursal_id', $sucursal->id)->latest('id')->first();
    expect((float) $factura->total)->toBe(70.00);

    $this->assertDatabaseHas('detalles', [
        'factura_id' => $factura->id,
        'producto_id' => $producto->id,
        'lote_id' => $loteAntiguo->id,
        'precio' => 35.00,
        'cantidad' => 2,
        'total' => 70.00,
    ]);
});

test('siat xml generator adheres to special codes 99001 99002 99003 and standard CI rules', function () {
    $user = User::factory()->admin()->create();
    $service = app(SiatFacturacionService::class);
    $sucursal = Sucursal::factory()->create(['codigo_sucursal' => 0]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Caja Central',
        'codigo' => 0,
        'estado' => 'ACTIVO',
    ]);
    $caja = Caja::factory()->create([
        'usuario_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
    ]);
    $cufd = Cufd::create([
        'usuario_creador_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'codigo_ambiente' => 2,
        'codigo' => 'TESTCUFD1234567890',
        'codigo_control' => 'CTRL123',
        'direccion' => 'Av. Central 123',
        'fecha_vigencia' => now()->addDay(),
    ]);

    // Caso 1: NIT 99001 (S/N o extranjero) -> tipo doc 5, doc 99001, codigoExcepcion 1
    $f99001 = Factura::create([
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'cufd_id' => $cufd->id,
        'caja_id' => $caja->id,
        'cliente_id' => 1,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '101',
        'nit' => '99001',
        'razon_social' => 'S/N',
        'fecha' => now(),
        'total' => 10.00,
        'cuf' => 'CUF101',
        'estado' => 'EMITIDA',
    ]);
    $xml1 = $service->construirXmlFacturaCompraVenta($f99001);
    expect($xml1)->toContain('<codigoTipoDocumentoIdentidad>5</codigoTipoDocumentoIdentidad>')
        ->and($xml1)->toContain('<numeroDocumento>99001</numeroDocumento>')
        ->and($xml1)->toContain('<codigoExcepcion>1</codigoExcepcion>')
        ->and($xml1)->toContain('<nombreRazonSocial>S/N</nombreRazonSocial>');

    // Caso 2: NIT 99002 (Control Tributario) -> tipo doc 5, doc 99002, codigoExcepcion 1, razonSocial Control Tributario
    $f99002 = Factura::create([
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'cufd_id' => $cufd->id,
        'caja_id' => $caja->id,
        'cliente_id' => null,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '102',
        'nit' => '99002',
        'razon_social' => 'Control Tributario',
        'fecha' => now(),
        'total' => 10.00,
        'cuf' => 'CUF102',
        'estado' => 'EMITIDA',
    ]);
    $xml2 = $service->construirXmlFacturaCompraVenta($f99002);
    expect($xml2)->toContain('<codigoTipoDocumentoIdentidad>5</codigoTipoDocumentoIdentidad>')
        ->and($xml2)->toContain('<numeroDocumento>99002</numeroDocumento>')
        ->and($xml2)->toContain('<codigoExcepcion>1</codigoExcepcion>')
        ->and($xml2)->toContain('<nombreRazonSocial>Control Tributario</nombreRazonSocial>');

    // Caso 3: NIT 99003 (Ventas Menores del Día) -> tipo doc 5, doc 99003, codigoExcepcion 1, razonSocial VENTAS MENORES DEL DÍA
    $f99003 = Factura::create([
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'cufd_id' => $cufd->id,
        'caja_id' => $caja->id,
        'cliente_id' => null,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '103',
        'nit' => '99003',
        'razon_social' => 'VENTAS MENORES DEL DÍA',
        'fecha' => now(),
        'total' => 10.00,
        'cuf' => 'CUF103',
        'estado' => 'EMITIDA',
    ]);
    $xml3 = $service->construirXmlFacturaCompraVenta($f99003);
    expect($xml3)->toContain('<codigoTipoDocumentoIdentidad>5</codigoTipoDocumentoIdentidad>')
        ->and($xml3)->toContain('<numeroDocumento>99003</numeroDocumento>')
        ->and($xml3)->toContain('<codigoExcepcion>1</codigoExcepcion>')
        ->and($xml3)->toContain('<nombreRazonSocial>VENTAS MENORES DEL DÍA</nombreRazonSocial>');

    // Caso 4: Cliente con Carnet de Identidad y Complemento -> tipo doc 1 (CI), codigoExcepcion 0, complemento en tag
    $clienteCi = Cliente::create([
        'nombres' => 'Carlos',
        'ap_paterno' => 'Mendoza',
        'cedula' => '4892015',
        'complemento' => '1A',
        'razon_social' => 'CARLOS MENDOZA',
        'estado' => 'ACTIVO',
    ]);
    $fCi = Factura::create([
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'cufd_id' => $cufd->id,
        'caja_id' => $caja->id,
        'cliente_id' => $clienteCi->id,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '104',
        'nit' => '4892015',
        'razon_social' => 'CARLOS MENDOZA',
        'fecha' => now(),
        'total' => 10.00,
        'cuf' => 'CUF104',
        'estado' => 'EMITIDA',
    ]);
    $xml4 = $service->construirXmlFacturaCompraVenta($fCi);
    expect($xml4)->toContain('<codigoTipoDocumentoIdentidad>1</codigoTipoDocumentoIdentidad>')
        ->and($xml4)->toContain('<numeroDocumento>4892015</numeroDocumento>')
        ->and($xml4)->toContain('<complemento>1A</complemento>')
        ->and($xml4)->toContain('<codigoExcepcion>0</codigoExcepcion>');
});

test('pos blocks sale equal or greater than 1000 Bs if customer is generic S/N (99001) per SIN regulation', function () {
    $user = User::factory()->admin()->create();
    $sucursal = Sucursal::factory()->create(['codigo_sucursal' => 0]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Caja Central 1',
        'codigo' => 0,
        'estado' => 'ACTIVO',
    ]);
    Cufd::create([
        'usuario_creador_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'codigo_ambiente' => 2,
        'codigo' => 'B0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0',
        'codigo_control' => 'CONTROL123456',
        'direccion' => 'Av. Central 123',
        'fecha_vigencia' => now()->addDay(),
        'estado' => 'ACTIVO',
    ]);
    $caja = Caja::factory()->create([
        'usuario_id' => $user->id,
        'sucursal_id' => $sucursal->id,
        'punto_venta_id' => $puntoVenta->id,
        'monto_apertura' => 100.00,
        'saldo' => 100.00,
        'estado' => 'ABIERTA',
    ]);

    $producto = Producto::create([
        'nombre' => 'Tratamiento Oncológico Especial',
        'codigo' => 'MED-1000',
        'precio_venta' => 1500.00,
        'precio_compra' => 1200.00,
        'stock_minimo' => 1,
        'unidad_medida_sin' => 57,
        'codigo_producto_sin' => '1003655',
        'actividad_economica' => '4772100',
        'estado' => 'ACTIVO',
    ]);

    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $sucursal->id,
        'numero_lote' => 'LOT-CARO-01',
        'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
        'stock_inicial' => 5,
        'stock_actual' => 5,
        'precio_compra' => 1200.00,
        'precio_venta' => 1500.00,
        'estado' => 'ACTIVO',
    ]);

    $clienteNominativo = Cliente::create([
        'nombres' => 'Fernando',
        'ap_paterno' => 'Quiroga',
        'cedula' => '5432109',
        'razon_social' => 'FERNANDO QUIROGA',
        'estado' => 'ACTIVO',
    ]);

    // 1. Intentar vender con cliente por defecto S/N (id = 1, nit = 99001) por Bs. 1.500
    Livewire::actingAs($user)
        ->test(Pos::class)
        ->call('seleccionarProducto', $producto->id)
        ->set('cantidadAgregar', 1)
        ->call('agregarAlCarrito')
        ->set('montoEfectivo', 1500.00)
        ->call('procesarVenta')
        ->assertDispatched('swal-alert', function (string $eventName, array $params) {
            return $params['icon'] === 'warning' && str_contains($params['title'], 'Identificación Obligatoria');
        });

    expect(Factura::count())->toBe(0);

    // 2. Ahora seleccionar cliente nominativo con CI 5432109 y procesar la venta
    Livewire::actingAs($user)
        ->test(Pos::class)
        ->call('seleccionarProducto', $producto->id)
        ->set('cantidadAgregar', 1)
        ->call('agregarAlCarrito')
        ->call('seleccionarCliente', $clienteNominativo->id)
        ->set('montoEfectivo', 1500.00)
        ->call('procesarVenta')
        ->assertDispatched('swal-alert', function (string $eventName, array $params) {
            return $params['icon'] === 'success';
        });

    expect(Factura::count())->toBe(1);
    $factura = Factura::first();
    expect((float) $factura->total)->toBe(1500.00)
        ->and($factura->nit)->toBe('5432109')
        ->and($factura->razon_social)->toBe('FERNANDO QUIROGA');
});
