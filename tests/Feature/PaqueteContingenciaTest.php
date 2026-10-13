<?php

use App\Livewire\Sincronizacion\EventosSignificativos;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\PaqueteFactura;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoEventoSignificativo;
use App\Models\User;
use App\Services\SiatFacturacionService;
use Livewire\Livewire;

beforeEach(function () {
    $this->sucursal = Sucursal::firstOrCreate(['id' => 1], [
        'nombre' => 'Casa Matriz',
        'codigo_sucursal' => 0,
        'direccion' => 'Av. Central 123',
        'estado' => 'ACTIVO',
    ]);

    $this->pv = PuntoVenta::firstOrCreate(['id' => 1], [
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Punto de Venta 1',
        'codigo' => 1,
        'estado' => 'ACTIVO',
    ]);

    $this->cufd = Cufd::firstOrCreate(['punto_venta_id' => $this->pv->id], [
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'B4CAFE22C74C11F18817E9075B2C26E4',
        'codigo_control' => 'CTRL123',
        'direccion' => 'Av. Central 123',
        'fecha_vigencia' => now()->addDays(1),
        'estado' => 'ACTIVO',
    ]);

    $this->cuis = Cuis::firstOrCreate(['punto_venta_id' => $this->pv->id], [
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'CUIS123456',
        'fecha_vigencia' => now()->addYear(),
        'estado' => 'ACTIVO',
    ]);

    $this->tipoEvento = TipoEventoSignificativo::firstOrCreate(['id' => 1], [
        'codigo_clasificador' => 1,
        'descripcion' => 'CORTE DEL SERVICIO DE INTERNET',
    ]);

    $this->cliente = Cliente::firstOrCreate(['id' => 1], [
        'razon_social' => 'CLIENTE TEST',
        'nit' => '1234567',
        'email' => 'cliente@test.com',
        'estado' => 'ACTIVO',
    ]);

    $this->caja = Caja::firstOrCreate(['id' => 1], [
        'sucursal_id' => $this->sucursal->id,
        'punto_venta_id' => $this->pv->id,
        'nombre' => 'Caja 1',
        'monto_apertura' => 100,
        'saldo' => 100,
        'estado' => 'ABIERTA',
    ]);

    $this->evento = EventoSignificativo::create([
        'punto_venta_id' => $this->pv->id,
        'tipo_evento_significativo_id' => $this->tipoEvento->id,
        'cufd_activo_id' => $this->cufd->id,
        'cufd_evento_id' => $this->cufd->id,
        'cui_id' => $this->cuis->id,
        'codigo_recepcion' => '9953999',
        'descripcion' => 'Contingencia de prueba por corte de internet',
        'fecha_ini' => now()->subHour()->format('Y-m-d H:i:s'),
        'fecha_fin' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
        'estado' => 'REGISTRADO',
    ]);
});

test('can open and close package modal on events', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->assertSet('showPaqueteModal', false)
        ->call('abrirModalPaquete', $this->evento->id)
        ->assertSet('showPaqueteModal', true)
        ->assertSet('paqueteEventoId', $this->evento->id)
        ->call('cerrarModalPaquete')
        ->assertSet('showPaqueteModal', false)
        ->assertSet('paqueteEventoId', null);
});

test('package modal displays pending contingency invoices and packages', function () {
    $user = User::factory()->admin()->create();

    $factura = Factura::create([
        'punto_venta_id' => $this->pv->id,
        'sucursal_id' => $this->sucursal->id,
        'cufd_id' => $this->cufd->id,
        'caja_id' => 1,
        'cliente_id' => 1,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '7788',
        'nit' => '1234567',
        'razon_social' => 'CLIENTE TEST',
        'fecha' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
        'total' => 150.00,
        'monto_total_subjeto_iva' => 150.00,
        'cuf' => 'CUFTEST12345',
        'estado' => 'PENDIENTE_ENVIO',
        'evento_significativo_id' => $this->evento->id,
        'productos_xml' => '<?xml version="1.0" encoding="UTF-8"?><facturaElectronicaCompraVenta></facturaElectronicaCompraVenta>',
    ]);

    $paquete = PaqueteFactura::create([
        'evento_significativo_id' => $this->evento->id,
        'punto_venta_id' => $this->pv->id,
        'cufd_id' => $this->cufd->id,
        'codigo_recepcion' => 'REC-PKG-99999',
        'codigo_estado' => 901,
        'estado' => 'PENDIENTE',
        'cantidad_facturas' => 1,
        'hash_archivo' => hash('sha256', 'test'),
        'nombre_archivo' => 'paquete_test.tar.gz',
        'fecha_envio' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->call('abrirModalPaquete', $this->evento->id)
        ->assertSee('7788')
        ->assertSee('CLIENTE TEST')
        ->assertSee('REC-PKG-99999')
        ->assertSee('901 PENDIENTE');
});

test('service constructs tar gz package from invoices', function () {
    $user = User::factory()->admin()->create();
    $service = app(SiatFacturacionService::class);

    $factura = Factura::create([
        'punto_venta_id' => $this->pv->id,
        'sucursal_id' => $this->sucursal->id,
        'cufd_id' => $this->cufd->id,
        'caja_id' => 1,
        'cliente_id' => 1,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '8899',
        'nit' => '1234567',
        'razon_social' => 'CLIENTE PRUEBA',
        'fecha' => now()->format('Y-m-d H:i:s'),
        'total' => 200.00,
        'monto_total_subjeto_iva' => 200.00,
        'cuf' => 'CUFPRUEBA999',
        'estado' => 'PENDIENTE_ENVIO',
        'productos_xml' => '<?xml version="1.0" encoding="UTF-8"?><facturaElectronicaCompraVenta><cabecera><nitEmisor>123</nitEmisor></cabecera></facturaElectronicaCompraVenta>',
    ]);

    $resultado = $service->construirPaqueteTarGz([$factura]);

    expect($resultado['error'])->toBeNull()
        ->and($resultado['cantidad'])->toBe(1)
        ->and($resultado['bytes'])->not->toBeEmpty()
        ->and($resultado['hash'])->toBe(hash('sha256', $resultado['bytes']));
});

test('service automatically generates xml with construirXmlFacturaCompraVenta when productos_xml is null', function () {
    $user = User::factory()->admin()->create();
    $service = app(SiatFacturacionService::class);

    $factura = Factura::create([
        'punto_venta_id' => $this->pv->id,
        'sucursal_id' => $this->sucursal->id,
        'cufd_id' => $this->cufd->id,
        'caja_id' => 1,
        'cliente_id' => 1,
        'usuario_creador_id' => $user->id,
        'numero_factura' => '9900',
        'nit' => '1234567',
        'razon_social' => 'CLIENTE REGENERAR XML',
        'fecha' => now()->format('Y-m-d H:i:s'),
        'total' => 100.00,
        'monto_total_subjeto_iva' => 100.00,
        'cuf' => 'CUFPRUEBA888',
        'estado' => 'PENDIENTE_ENVIO',
        'productos_xml' => null, // Explicitly null to trigger fallback
    ]);

    $xml = $service->construirXmlFacturaCompraVenta($factura);

    expect($xml)->toContain('<facturaComputarizadaCompraVenta')
        ->and($xml)->toContain('<numeroFactura>9900</numeroFactura>')
        ->and($xml)->toContain('<cuf>CUFPRUEBA888</cuf>');

    // Paquete Tar.gz should now also process successfully with null productos_xml
    $factura->productos_xml = null;
    $resultado = $service->construirPaqueteTarGz([$factura]);

    expect($resultado['error'])->toBeNull()
        ->and($resultado['cantidad'])->toBe(1)
        ->and($resultado['bytes'])->not->toBeEmpty();
});
