<?php

namespace App\Livewire\Ventas;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Services\SiatFacturacionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Pos extends Component
{
    // Datos de la Caja del Usuario
    public ?int $cajaId = null;

    public ?int $sucursalId = null;

    public ?int $puntoVentaId = null;

    // Buscador de Medicamentos
    public string $productoSearch = '';

    public ?int $selectedProductoId = null;

    public mixed $selectedLoteId = 'AUTO_FEFO';

    public float|string $cantidadAgregar = 1;

    public float|string $descuentoItemAgregar = 0;

    // Carrito de Ventas
    // Estructura de cada item:
    // ['producto_id', 'lote_id', 'nombre', 'codigo', 'numero_lote', 'fecha_vencimiento', 'precio', 'cantidad', 'descuento', 'total', 'stock_max']
    public array $carrito = [];

    // Descuento General
    public float|string $descuentoGeneral = 0;

    // Buscador y Selección de Cliente
    public string $clienteSearch = '';

    public ?int $clienteId = null;

    public string $clienteNombre = 'Sin Nombre';

    public string $clienteDocumento = '99001';

    public string $clienteRazonSocial = 'S/N';

    // Modal de Registro Rápido de Nuevo Cliente
    public bool $showNuevoClienteModal = false;

    public string $nuevoClienteNombres = '';

    public string $nuevoClienteApellidos = '';

    public string $nuevoClienteCedula = '';

    public string $nuevoClienteComplemento = '';

    public string $nuevoClienteNit = '';

    public string $nuevoClienteRazonSocial = '';

    public string $nuevoClienteCelular = '';

    // Métodos de Pago
    public float|string $montoEfectivo = 0;

    public float|string $montoQr = 0;

    public float|string $montoTransferencia = 0;

    // Modal de Impresión / Factura Emitida
    public bool $showTicketModal = false;

    public ?int $facturaEmitidaId = null;

    public function mount(): void
    {
        $this->verificarCaja();
        $this->asignarClienteDefault();
    }

    public function verificarCaja(): void
    {
        $caja = Caja::obtenerCajaAbierta(Auth::id());
        if ($caja) {
            $this->cajaId = $caja->id;
            $this->sucursalId = $caja->sucursal_id;
            $this->puntoVentaId = $caja->punto_venta_id;
        } else {
            $this->cajaId = null;
            $this->sucursalId = null;
            $this->puntoVentaId = null;
        }
    }

    public function asignarClienteDefault(): void
    {
        $clienteDefault = Cliente::find(1);
        if ($clienteDefault) {
            $this->clienteId = $clienteDefault->id;
            $this->clienteNombre = $clienteDefault->nombres ?: 'Sin Nombre';
            $this->clienteDocumento = $clienteDefault->nit ?: ($clienteDefault->cedula ?: '99001');
            $this->clienteRazonSocial = $clienteDefault->razon_social ?: 'S/N';
        } else {
            $this->clienteId = 1;
            $this->clienteNombre = 'Sin Nombre';
            $this->clienteDocumento = '99001';
            $this->clienteRazonSocial = 'S/N';
        }
    }

    public function seleccionarProducto(int $id): void
    {
        $prod = Producto::find($id);
        if (! $prod) {
            return;
        }

        $this->selectedProductoId = $prod->id;
        $this->productoSearch = '';
        $this->cantidadAgregar = 1;
        $this->descuentoItemAgregar = 0;

        $this->selectedLoteId = 'AUTO_FEFO';
    }

    public function cancelarSeleccionProducto(): void
    {
        $this->selectedProductoId = null;
        $this->selectedLoteId = 'AUTO_FEFO';
        $this->cantidadAgregar = 1;
        $this->descuentoItemAgregar = 0;
    }

    public function agregarAlCarrito(): void
    {
        if (! $this->cajaId) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Caja Requerida', message: 'Debe abrir caja antes de agregar productos al carrito.');

            return;
        }

        $this->validate([
            'selectedProductoId' => 'required|exists:productos,id',
            'cantidadAgregar' => 'required|numeric|min:0.1',
            'descuentoItemAgregar' => 'nullable|numeric|min:0',
        ], [
            'cantidadAgregar.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $prod = Producto::findOrFail($this->selectedProductoId);
        $cantRequerida = (float) $this->cantidadAgregar;
        $descTotal = (float) ($this->descuentoItemAgregar ?: 0);

        // REGLA FUNDAMENTAL DE NEGOCIO: El precio de venta a cobrar es SIEMPRE el precio actualizado del producto (productos.precio_venta).
        // Los lotes aportan trazabilidad física y caducidad, pero NUNCA el precio de venta final si difiere del maestro.
        $precioUnitarioVenta = (float) ($prod->precio_venta > 0 ? $prod->precio_venta : 0);

        // Mapear existencias actualmente en el carrito por lote
        $stockEnCarritoPorLote = [];
        foreach ($this->carrito as $item) {
            $lId = (int) $item['lote_id'];
            $stockEnCarritoPorLote[$lId] = ($stockEnCarritoPorLote[$lId] ?? 0) + (float) $item['cantidad'];
        }

        $esAutoFefo = empty($this->selectedLoteId) || $this->selectedLoteId === 'AUTO_FEFO';

        if ($esAutoFefo) {
            // Obtener todos los lotes vigentes de esta sucursal ordenados por FEFO (vencimiento más próximo primero)
            $lotesDisponibles = Lote::where('producto_id', $prod->id)
                ->where('sucursal_id', $this->sucursalId)
                ->where('stock_actual', '>', 0)
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->whereIn('estado', ['ACTIVO', 'OPTIMO', 'PROXIMO_VENCER'])
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();

            if ($lotesDisponibles->isEmpty()) {
                $this->addError('cantidadAgregar', 'No existen lotes vigentes con existencia disponible para este producto en la sucursal.');

                return;
            }

            // Calcular stock real disponible restante entre todos los lotes
            $stockTotalReal = 0;
            foreach ($lotesDisponibles as $l) {
                $stockTotalReal += max(0, (float) $l->stock_actual - ($stockEnCarritoPorLote[$l->id] ?? 0));
            }

            if ($cantRequerida > $stockTotalReal) {
                $this->addError('cantidadAgregar', "Existencia insuficiente. Solo dispone de {$stockTotalReal} unidades entre todos los lotes vigentes de la sucursal.");

                return;
            }

            if ($descTotal >= ($precioUnitarioVenta * $cantRequerida)) {
                $this->addError('descuentoItemAgregar', 'El descuento no puede ser igual o mayor al total del ítem.');

                return;
            }

            $cantRestante = $cantRequerida;
            $descuentoPorUnidad = $cantRequerida > 0 ? ($descTotal / $cantRequerida) : 0;

            foreach ($lotesDisponibles as $lote) {
                $disp = max(0, (float) $lote->stock_actual - ($stockEnCarritoPorLote[$lote->id] ?? 0));
                if ($disp <= 0) {
                    continue;
                }

                $tomar = min($cantRestante, $disp);
                $descLote = round($descuentoPorUnidad * $tomar, 2);
                $precioItem = $precioUnitarioVenta > 0 ? $precioUnitarioVenta : (float) $lote->precio_venta;
                $subtotalLote = max(0, round(($precioItem * $tomar) - $descLote, 2));

                // Buscar si este lote específico ya está en el carrito
                $idxEncontrado = null;
                foreach ($this->carrito as $idx => $it) {
                    if ($it['producto_id'] === $prod->id && $it['lote_id'] === $lote->id) {
                        $idxEncontrado = $idx;
                        break;
                    }
                }

                if ($idxEncontrado !== null) {
                    $nuevaCant = $this->carrito[$idxEncontrado]['cantidad'] + $tomar;
                    $nuevoDesc = $this->carrito[$idxEncontrado]['descuento'] + $descLote;
                    $this->carrito[$idxEncontrado]['cantidad'] = $nuevaCant;
                    $this->carrito[$idxEncontrado]['descuento'] = $nuevoDesc;
                    $this->carrito[$idxEncontrado]['precio'] = $precioItem;
                    $this->carrito[$idxEncontrado]['total'] = max(0, round(($precioItem * $nuevaCant) - $nuevoDesc, 2));
                } else {
                    $this->carrito[] = [
                        'producto_id' => $prod->id,
                        'lote_id' => $lote->id,
                        'nombre' => $prod->nombre,
                        'codigo' => $prod->codigo,
                        'numero_lote' => $lote->numero_lote,
                        'fecha_vencimiento' => Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y'),
                        'precio' => $precioItem,
                        'cantidad' => $tomar,
                        'descuento' => $descLote,
                        'total' => $subtotalLote,
                        'stock_max' => (float) $lote->stock_actual,
                    ];
                }

                $cantRestante -= $tomar;
                if ($cantRestante <= 0) {
                    break;
                }
            }
        } else {
            // Despacho desde un lote específico seleccionado manualmente
            $lote = Lote::where('id', (int) $this->selectedLoteId)
                ->where('sucursal_id', $this->sucursalId)
                ->first();

            if (! $lote) {
                $this->addError('selectedLoteId', 'El lote seleccionado no existe.');

                return;
            }

            $dispLote = max(0, (float) $lote->stock_actual - ($stockEnCarritoPorLote[$lote->id] ?? 0));
            if ($cantRequerida > $dispLote) {
                $this->addError('cantidadAgregar', "Existencia insuficiente en el lote {$lote->numero_lote}. Solo dispone de {$dispLote} unidades.");

                return;
            }

            $precioItem = $precioUnitarioVenta > 0 ? $precioUnitarioVenta : (float) $lote->precio_venta;
            if ($descTotal >= ($precioItem * $cantRequerida)) {
                $this->addError('descuentoItemAgregar', 'El descuento no puede ser igual o mayor al total del ítem.');

                return;
            }

            $subtotalLote = max(0, round(($precioItem * $cantRequerida) - $descTotal, 2));

            $idxEncontrado = null;
            foreach ($this->carrito as $idx => $it) {
                if ($it['producto_id'] === $prod->id && $it['lote_id'] === $lote->id) {
                    $idxEncontrado = $idx;
                    break;
                }
            }

            if ($idxEncontrado !== null) {
                $nuevaCant = $this->carrito[$idxEncontrado]['cantidad'] + $cantRequerida;
                $nuevoDesc = $this->carrito[$idxEncontrado]['descuento'] + $descTotal;
                $this->carrito[$idxEncontrado]['cantidad'] = $nuevaCant;
                $this->carrito[$idxEncontrado]['descuento'] = $nuevoDesc;
                $this->carrito[$idxEncontrado]['precio'] = $precioItem;
                $this->carrito[$idxEncontrado]['total'] = max(0, round(($precioItem * $nuevaCant) - $nuevoDesc, 2));
            } else {
                $this->carrito[] = [
                    'producto_id' => $prod->id,
                    'lote_id' => $lote->id,
                    'nombre' => $prod->nombre,
                    'codigo' => $prod->codigo,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_vencimiento' => Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y'),
                    'precio' => $precioItem,
                    'cantidad' => $cantRequerida,
                    'descuento' => $descTotal,
                    'total' => $subtotalLote,
                    'stock_max' => (float) $lote->stock_actual,
                ];
            }
        }

        $this->cancelarSeleccionProducto();
        $this->actualizarMontosPago();
    }

    public function modificarCantidad(int $index, float $delta): void
    {
        if (! isset($this->carrito[$index])) {
            return;
        }

        $item = &$this->carrito[$index];
        $nuevaCantidad = $item['cantidad'] + $delta;

        if ($nuevaCantidad <= 0) {
            $this->eliminarDelCarrito($index);

            return;
        }

        if ($nuevaCantidad > $item['stock_max']) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Stock Límite', message: "Solo hay {$item['stock_max']} unidades disponibles en este lote.");

            return;
        }

        $item['cantidad'] = $nuevaCantidad;
        $item['total'] = max(0, round(($item['precio'] * $nuevaCantidad) - $item['descuento'], 2));
        $this->actualizarMontosPago();
    }

    public function eliminarDelCarrito(int $index): void
    {
        if (isset($this->carrito[$index])) {
            unset($this->carrito[$index]);
            $this->carrito = array_values($this->carrito);
            $this->actualizarMontosPago();
        }
    }

    public function limpiarCarrito(): void
    {
        $this->carrito = [];
        $this->descuentoGeneral = 0;
        $this->montoEfectivo = 0;
        $this->montoQr = 0;
        $this->montoTransferencia = 0;
        $this->cancelarSeleccionProducto();
        $this->asignarClienteDefault();
    }

    public function updatedDescuentoGeneral(): void
    {
        $this->actualizarMontosPago();
    }

    public function actualizarMontosPago(): void
    {
        $total = $this->calcularTotal();
        // Por defecto, sugerir pago en efectivo por el total formateado a 2 decimales exactos
        $this->montoEfectivo = number_format($total, 2, '.', '');
        $this->montoQr = 0;
        $this->montoTransferencia = 0;
    }

    public function calcularSubtotal(): float
    {
        $sum = 0;
        foreach ($this->carrito as $item) {
            $sum += (float) $item['total'];
        }

        return round($sum, 2);
    }

    public function calcularTotal(): float
    {
        $subtotal = $this->calcularSubtotal();
        $descGen = (float) $this->descuentoGeneral;

        return max(0, round($subtotal - $descGen, 2));
    }

    public function ajustarSaldoMetodo(string $metodo): void
    {
        $total = round($this->calcularTotal(), 2);
        $ef = round((float) $this->montoEfectivo, 2);
        $qr = round((float) $this->montoQr, 2);
        $tr = round((float) $this->montoTransferencia, 2);

        if ($metodo === 'efectivo') {
            $restante = max(0, round($total - ($qr + $tr), 2));
            $this->montoEfectivo = number_format($restante, 2, '.', '');
        } elseif ($metodo === 'qr') {
            $restante = max(0, round($total - ($ef + $tr), 2));
            $this->montoQr = number_format($restante, 2, '.', '');
        } elseif ($metodo === 'transferencia') {
            $restante = max(0, round($total - ($ef + $qr), 2));
            $this->montoTransferencia = number_format($restante, 2, '.', '');
        }
    }

    public function seleccionarCliente(int $id): void
    {
        $cliente = Cliente::find($id);
        if ($cliente) {
            $this->clienteId = $cliente->id;
            $this->clienteNombre = $cliente->nombres ?: 'Sin Nombre';
            $this->clienteDocumento = $cliente->nit ?: ($cliente->cedula ?: '99001');
            $this->clienteRazonSocial = $cliente->razon_social ?: ($cliente->nombres ?: 'S/N');
            $this->clienteSearch = '';
        }
    }

    public function abrirModalNuevoCliente(): void
    {
        $this->resetErrorBag();
        $this->nuevoClienteNombres = '';
        $this->nuevoClienteApellidos = '';
        $this->nuevoClienteCedula = '';
        $this->nuevoClienteComplemento = '';
        $this->nuevoClienteNit = '';
        $this->nuevoClienteRazonSocial = '';
        $this->nuevoClienteCelular = '';
        $this->showNuevoClienteModal = true;
    }

    public function updatedNuevoClienteNombres(): void
    {
        $this->sugerirRazonSocial();
    }

    public function updatedNuevoClienteApellidos(): void
    {
        $this->sugerirRazonSocial();
    }

    private function sugerirRazonSocial(): void
    {
        $nombreCompleto = trim("{$this->nuevoClienteNombres} {$this->nuevoClienteApellidos}");
        if (empty($this->nuevoClienteRazonSocial) || $this->nuevoClienteRazonSocial === strtoupper(trim($this->nuevoClienteNombres))) {
            $this->nuevoClienteRazonSocial = strtoupper($nombreCompleto);
        }
    }

    public function guardarNuevoCliente(): void
    {
        $this->validate([
            'nuevoClienteNombres' => 'required|string|min:2|max:100',
            'nuevoClienteApellidos' => 'nullable|string|max:100',
            'nuevoClienteCedula' => 'nullable|string|max:30',
            'nuevoClienteComplemento' => 'nullable|string|max:5',
            'nuevoClienteNit' => 'nullable|string|max:30',
            'nuevoClienteRazonSocial' => 'required|string|min:2|max:150',
            'nuevoClienteCelular' => 'nullable|string|max:30',
        ], [
            'nuevoClienteNombres.required' => 'El nombre del cliente es obligatorio.',
            'nuevoClienteRazonSocial.required' => 'La razón social para la factura es obligatoria.',
        ]);

        $cedula = trim($this->nuevoClienteCedula);
        $nit = trim($this->nuevoClienteNit);
        $complemento = trim($this->nuevoClienteComplemento);

        if (empty($cedula) && empty($nit)) {
            $this->addError('nuevoClienteCedula', 'Debe registrar al menos la Cédula de Identidad o el NIT.');
            $this->addError('nuevoClienteNit', 'Debe registrar al menos la Cédula de Identidad o el NIT.');

            return;
        }

        $cliente = Cliente::create([
            'nombres' => trim($this->nuevoClienteNombres),
            'ap_paterno' => trim($this->nuevoClienteApellidos),
            'cedula' => $cedula ?: null,
            'complemento' => ($cedula && $complemento) ? strtoupper($complemento) : null,
            'nit' => $nit ?: null,
            'razon_social' => strtoupper(trim($this->nuevoClienteRazonSocial)),
            'numero_celular' => trim($this->nuevoClienteCelular) ?: null,
            'estado' => 'ACTIVO',
            'usuario_creador_id' => Auth::id(),
        ]);

        $this->seleccionarCliente($cliente->id);
        $this->showNuevoClienteModal = false;
        $this->dispatch('swal-alert', icon: 'success', title: 'Cliente Registrado', message: 'Cliente registrado y vinculado a la venta actual.');
    }

    /**
     * Procesar Cobro y Emisión de Factura Computarizada en Línea SIAT
     */
    public function procesarVenta(SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.pos.vender'), 403, 'No tiene permiso para procesar ventas.');

        $this->verificarCaja();
        if (! $this->cajaId) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Caja No Aperturada', message: 'Debe aperturar su turno de caja antes de emitir facturas.');

            return;
        }

        if (empty($this->carrito)) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Carrito Vacío', message: 'Debe agregar al menos un medicamento para cobrar.');

            return;
        }

        $totalAPagar = round($this->calcularTotal(), 2);

        // Control Normativo SIAT (RND 102100000011 / RND 102300000021):
        // Para ventas iguales o superiores a Bs. 1.000, es obligatorio consignar C.I. o NIT y Nombre/Razón Social.
        // Queda prohibida la emisión a "Sin Nombre" (99001).
        $esSinNombre = ($this->clienteId === 1 || trim((string) $this->clienteDocumento) === '99001' || strtoupper(trim((string) $this->clienteRazonSocial)) === 'S/N' || empty(trim((string) $this->clienteDocumento)));
        if ($totalAPagar >= 1000.00 && $esSinNombre) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Identificación Obligatoria (SIN)', message: 'Según la normativa tributaria (RND 102100000011 / RND 102300000021), para ventas iguales o superiores a Bs. 1.000 es obligatorio registrar los datos nominativos del cliente (C.I. o NIT con Nombres/Razón Social). No se permite emitir con S/N ni NIT 99001.');

            return;
        }

        $pagoEf = round((float) $this->montoEfectivo, 2);
        $pagoQr = round((float) $this->montoQr, 2);
        $pagoTransf = round((float) $this->montoTransferencia, 2);
        $totalPagado = round($pagoEf + $pagoQr + $pagoTransf, 2);

        if ($totalPagado < round($totalAPagar - 0.009, 2)) {
            $falta = max(0, round($totalAPagar - $totalPagado, 2));
            $this->dispatch('swal-alert', icon: 'warning', title: 'Pago Insuficiente', message: 'El total pagado no cubre el monto de la compra. Faltan Bs. '.number_format($falta, 2));

            return;
        }

        $cambioEfectivo = max(0, round($pagoEf - max(0, $totalAPagar - ($pagoQr + $pagoTransf)), 2));

        // Resolver Punto de Venta y Sucursal
        $caja = Caja::findOrFail($this->cajaId);
        $sucursal = $caja->sucursal ?? Sucursal::first();
        $puntoVenta = $caja->puntoVenta ?? PuntoVenta::where('sucursal_id', $sucursal->id)->first();

        // Obtener CUFD vigente
        $cufd = $siatService->obtenerCufdVigente($sucursal->id, $puntoVenta->id);
        if (! $cufd) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Sin CUFD Vigente', message: 'No se pudo obtener un CUFD vigente para el punto de venta.');

            return;
        }

        // Obtener Correlativo y Parámetros para CUF
        $numeroFactura = $siatService->obtenerSiguienteNumeroFactura($sucursal->id, $puntoVenta->id);
        $fechaActual = now();
        $fechaHoraCadena = $fechaActual->format('YmdHis').'000';
        $nitEmisor = config('siat.nit');
        $codigoModalidad = (int) config('siat.codigo_modalidad');

        $cuf = $siatService->calcularCuf(
            $nitEmisor,
            $fechaHoraCadena,
            (int) $sucursal->codigo_sucursal,
            $codigoModalidad,
            1, // Tipo Emisión 1 (En Línea)
            1, // Tipo Factura 1 (Con derecho a Crédito Fiscal)
            1, // Tipo Documento Sector 1 (Factura Compra Venta)
            $numeroFactura,
            (int) $puntoVenta->codigo,
            $cufd->codigo_control
        );

        // Determinar Método de Pago principal para cabecera fiscal SIAT (1 = EFECTIVO, 2 = TARJETA, 7 = TRANSFERENCIA/QR)
        $metodoPagoSiat = 1;
        if ($pagoQr > 0 && $pagoEf == 0) {
            $metodoPagoSiat = 7;
        } elseif ($pagoTransf > 0 && $pagoEf == 0) {
            $metodoPagoSiat = 7;
        }

        DB::beginTransaction();
        try {
            // 1. Crear Registro de Factura
            $factura = Factura::create([
                'usuario_creador_id' => Auth::id(),
                'cufd_id' => $cufd->id,
                'sucursal_id' => $sucursal->id,
                'caja_id' => $caja->id,
                'cliente_id' => $this->clienteId ?: 1,
                'punto_venta_id' => $puntoVenta->id,
                'fecha' => $fechaActual,
                'nit' => $this->clienteDocumento ?: '99001',
                'razon_social' => $this->clienteRazonSocial ?: 'S/N',
                'numero_factura' => (string) $numeroFactura,
                'facturado' => 'SI',
                'codigo_metodo_pago' => $metodoPagoSiat,
                'total' => $totalAPagar,
                'monto_total_subjeto_iva' => $totalAPagar,
                'descuento_adicional' => (float) $this->descuentoGeneral,
                'cuf' => $cuf,
                'tipo_factura' => '1',
                'estado_pago' => 'PAGADO',
                'estado' => 'EMITIDA',
            ]);

            $detallesCreados = [];

            // 2. Descontar Stock FEFO y Registrar Detalles + Movimientos en Kardex
            foreach ($this->carrito as $item) {
                $lote = Lote::where('id', $item['lote_id'])->lockForUpdate()->firstOrFail();
                $cantPedida = (float) $item['cantidad'];

                if ($cantPedida > (float) $lote->stock_actual) {
                    throw new Exception("Existencia insuficiente durante el cobro en el lote {$lote->numero_lote}. Stock disponible: {$lote->stock_actual}");
                }

                $nuevoStock = (float) $lote->stock_actual - $cantPedida;
                $lote->stock_actual = $nuevoStock;
                if ($nuevoStock <= 0) {
                    $lote->estado = 'AGOTADO';
                }
                $lote->usuario_modificador_id = Auth::id();
                $lote->save();

                // Detalle de Factura
                $detalle = Detalle::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'producto_id' => $item['producto_id'],
                    'lote_id' => $lote->id,
                    'sucursal_id' => $sucursal->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'nombre_producto' => $item['nombre'],
                    'numero_lote' => $lote->numero_lote,
                    'fecha_vencimiento' => $lote->fecha_vencimiento,
                    'precio' => $item['precio'],
                    'cantidad' => $cantPedida,
                    'descuento' => $item['descuento'],
                    'total' => $item['total'],
                    'importe' => $item['total'],
                    'fecha' => $fechaActual,
                    'estado' => 'ACTIVO',
                ]);
                $detallesCreados[] = $detalle;

                // Movimiento en Kardex
                Movimiento::create([
                    'producto_id' => $item['producto_id'],
                    'sucursal_id' => $sucursal->id,
                    'lote_id' => $lote->id,
                    'tipo_movimiento' => 'VENTA',
                    'ingreso' => 0,
                    'salida' => $cantPedida,
                    'saldo_stock' => $nuevoStock,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_caducidad' => $lote->fecha_vencimiento,
                    'fecha' => $fechaActual,
                    'descripcion' => "Venta en mostrador según Factura N° {$numeroFactura}",
                    'precio_compra' => (float) $lote->precio_compra,
                    'precio_venta' => (float) $item['precio'],
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                ]);
            }

            // 3. Registrar Pagos
            if ($pagoEf > 0) {
                Pago::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'EFECTIVO',
                    'monto' => $pagoEf,
                    'cambio' => $cambioEfectivo,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago en efectivo Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);
            }

            if ($pagoQr > 0) {
                Pago::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'QR',
                    'monto' => $pagoQr,
                    'cambio' => 0,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago con código QR Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);
            }

            if ($pagoTransf > 0) {
                Pago::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'TRANSFERENCIA',
                    'monto' => $pagoTransf,
                    'cambio' => 0,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago por transferencia Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);
            }

            // 4. Actualizar Acumuladores de Caja
            $efectivoRealIngresado = max(0, $pagoEf - $cambioEfectivo);
            $caja->total_venta = (float) $caja->total_venta + $totalAPagar;
            $caja->venta_contado = (float) $caja->venta_contado + $efectivoRealIngresado;
            $caja->total_qr = (float) $caja->total_qr + $pagoQr;
            $caja->total_transferencia = (float) $caja->total_transferencia + $pagoTransf;
            $caja->saldo = (float) $caja->monto_apertura + (float) $caja->venta_contado + (float) $caja->otro_ingreso - (float) $caja->total_salida;
            $caja->usuario_modificador_id = Auth::id();
            $caja->save();

            // 5. Generar XML y Enviar a SIAT
            $xml = $siatService->generarXmlFactura($factura, $detallesCreados, $cufd, $sucursal, $puntoVenta);
            $factura->productos_xml = $xml;

            // Intentar envío SOAP a SIAT en tiempo real
            $resSiat = $siatService->enviarFacturaSiat($factura, $xml);
            if ($resSiat['success']) {
                $factura->codigo_recepcion = $resSiat['codigo_recepcion'];
                $factura->codigo_transaccion = 'VALIDADA';
                $factura->codigo_descripcion = 'VALIDADA';
                $factura->estado = 'VALIDADA';
            } else {
                $factura->codigo_recepcion = $resSiat['codigo_recepcion'] ?? null;
                $factura->codigo_descripcion = mb_substr($resSiat['codigo_descripcion'] ?? ($resSiat['mensaje'] ?? 'PENDIENTE'), 0, 255);
                $factura->descripcion = $resSiat['mensaje'] ?? null;
                $factura->estado = $resSiat['en_contingencia'] ? 'PENDIENTE_ENVIO' : 'OBSERVADA';
            }
            $factura->save();

            DB::commit();

            // Desplegar Ticket y Limpiar Carrito
            $this->facturaEmitidaId = $factura->id;
            $this->showTicketModal = true;
            $this->limpiarCarrito();

            $this->dispatch('abrir-ticket-flotante', url: route('ventas.factura.imprimir', $factura->id));

            if ($factura->estado === 'VALIDADA') {
                $icon = 'success';
                $title = '¡Venta y Factura Validada!';
                $msg = "¡Factura N° {$numeroFactura} emitida y validada exitosamente ante el SIAT!";
            } elseif ($factura->estado === 'OBSERVADA') {
                $icon = 'warning';
                $title = 'Venta Registrada con Observación';
                $motivo = ! empty($resSiat['mensaje']) && $resSiat['mensaje'] !== '[]' ? $resSiat['mensaje'] : 'Revisar observaciones en Historial de Ventas';
                $msg = "La venta fue registrada localmente, pero el SIAT reportó observaciones:\n{$motivo}";
            } else {
                $icon = 'success';
                $title = '¡Venta en Contingencia Realizada!';
                $motivo = ! empty($resSiat['mensaje']) && $resSiat['mensaje'] !== '[]' ? "\n\nRespuesta del SIAT:\n{$resSiat['mensaje']}" : '';
                $msg = "¡Venta completada con éxito!\nFactura registrada fuera de línea ({$factura->estado}).{$motivo}\n\nSerá regularizada automáticamente mediante el paquete de contingencia.";
            }

            $this->dispatch('swal-alert', icon: $icon, title: $title, message: $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al procesar venta POS: '.$e->getMessage());
            $this->dispatch('swal-alert', icon: 'error', title: 'Error al Facturar', message: 'No se pudo completar la venta: '.$e->getMessage());
        }
    }

    public function render(): View
    {
        $caja = Caja::obtenerCajaAbierta(Auth::id());

        // Búsqueda de Productos con stock disponible en la sucursal del cajero
        $productosEncontrados = collect();
        if (strlen(trim($this->productoSearch)) >= 2 && $this->sucursalId) {
            $productosEncontrados = Producto::with(['lotes' => function ($q) {
                $q->where('sucursal_id', $this->sucursalId)
                    ->where('stock_actual', '>', 0)
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                    ->whereIn('estado', ['ACTIVO', 'OPTIMO', 'PROXIMO_VENCER'])
                    ->orderBy('fecha_vencimiento', 'asc');
            }])
                ->where(function ($q) {
                    $q->where('nombre', 'like', "%{$this->productoSearch}%")
                        ->orWhere('codigo', 'like', "%{$this->productoSearch}%")
                        ->orWhere('principio_activo', 'like', "%{$this->productoSearch}%");
                })
                ->where('estado', 'ACTIVO')
                ->take(8)
                ->get();
        }

        // Lotes del producto actualmente seleccionado
        $lotesProducto = collect();
        if ($this->selectedProductoId && $this->sucursalId) {
            $lotesProducto = Lote::where('producto_id', $this->selectedProductoId)
                ->where('sucursal_id', $this->sucursalId)
                ->where('stock_actual', '>', 0)
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->whereIn('estado', ['ACTIVO', 'OPTIMO', 'PROXIMO_VENCER'])
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();
        }

        // Búsqueda de Clientes
        $clientesEncontrados = collect();
        if (strlen(trim($this->clienteSearch)) >= 2) {
            $clientesEncontrados = Cliente::where(function ($q) {
                $q->where('nombres', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('ap_paterno', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('nit', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('cedula', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('razon_social', 'like', "%{$this->clienteSearch}%");
            })
                ->where('estado', 'ACTIVO')
                ->take(6)
                ->get();
        }

        $facturaReciente = $this->facturaEmitidaId ? Factura::with(['detalles', 'pagos', 'cliente', 'sucursal'])->find($this->facturaEmitidaId) : null;

        return view('livewire.ventas.pos', [
            'caja' => $caja,
            'productosEncontrados' => $productosEncontrados,
            'lotesProducto' => $lotesProducto,
            'clientesEncontrados' => $clientesEncontrados,
            'facturaReciente' => $facturaReciente,
            'subtotalCarrito' => $this->calcularSubtotal(),
            'totalCarrito' => $this->calcularTotal(),
        ]);
    }
}
