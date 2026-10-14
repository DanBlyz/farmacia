<div class="space-y-6">
    <x-slot name="header">
        Historial de Ventas y Facturación
    </x-slot>

    <!-- Notificación Flash -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-teal-800 dark:text-teal-200 flex items-center justify-between text-sm shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-teal-500 text-base"></i>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-teal-500 hover:text-teal-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- 1. Tarjetas KPI Métricas de Ventas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $totalEmitidas = \App\Models\Factura::count();
            $totalMonto = (float) \App\Models\Factura::where('estado', '!=', 'ANULADA')->sum('total');
            $totalAnuladas = \App\Models\Factura::where('estado', 'ANULADA')->count();
            $totalValidadas = \App\Models\Factura::where('estado', 'VALIDADA')->count();
        @endphp

        <!-- Total Facturas -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Facturas</span>
                <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <i class="fas fa-receipt text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-800 dark:text-white">
                    {{ number_format($totalEmitidas, 0) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Registros emitidos en total</p>
            </div>
        </div>

        <!-- Total Facturado Neto -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Facturado Neto</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-money-bill-wave text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                    Bs. {{ number_format($totalMonto, 2) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Excluye facturas anuladas</p>
            </div>
        </div>

        <!-- Validadas por SIAT -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Validadas SIAT</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-check-double text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                    {{ number_format($totalValidadas, 0) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Aprobadas por Impuestos</p>
            </div>
        </div>

        <!-- Anuladas -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Anuladas</span>
                <span class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                    <i class="fas fa-ban text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">
                    {{ number_format($totalAnuladas, 0) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Con retorno a inventario</p>
            </div>
        </div>
    </div>

    <!-- 2. Tabla Clásica Enmarcada: Historial de Facturas -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800/80 p-5 sm:p-6 space-y-4">
        <!-- Barra de Filtros Superior -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Izquierda: Selector de Registros + Filtros -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Mostrar</span>
                    <select wire:model.live="perPage" 
                            class="py-1.5 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <!-- Filtro Sucursal -->
                <select wire:model.live="filtroSucursalId"
                        class="py-1.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    <option value="">Todas las Sucursales</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>

                <!-- Filtro Estado -->
                <select wire:model.live="filtroEstado"
                        class="py-1.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    <option value="">Todos los Estados</option>
                    <option value="VALIDADA">Validadas (SIAT)</option>
                    <option value="EMITIDA">Emitidas</option>
                    <option value="PENDIENTE_ENVIO">Pendientes / Contingencia</option>
                    <option value="ANULADA">Anuladas</option>
                </select>

                <!-- Fechas -->
                <input type="date" wire:model.live="fechaInicio" title="Desde"
                       class="py-1.5 px-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300">
                <input type="date" wire:model.live="fechaFin" title="Hasta"
                       class="py-1.5 px-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300">
            </div>

            <!-- Derecha: Buscador con Botón Limpiar -->
            <div class="relative w-full md:w-64">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar N° Factura, NIT, Razón..."
                       class="w-full py-2 pl-9 pr-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- Contenedor Enmarcado de la Tabla -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Factura N°</th>
                            <th class="py-3 px-4">Fecha y Hora</th>
                            <th class="py-3 px-4">Cliente / Razón Social</th>
                            <th class="py-3 px-4">NIT / C.I.</th>
                            <th class="py-3 px-4">Sucursal</th>
                            <th class="py-3 px-4">Cajero</th>
                            <th class="py-3 px-4 text-right">Total (Bs.)</th>
                            <th class="py-3 px-4 text-center">Estado SIAT</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                        @forelse($facturas as $factura)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors {{ $factura->estado === 'ANULADA' ? 'opacity-60 bg-rose-50/20' : '' }}">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">
                                    N° {{ $factura->numero_factura }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-800 dark:text-white">
                                        {{ $factura->fecha ? $factura->fecha->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        {{ $factura->fecha ? $factura->fecha->format('H:i') : '' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-white truncate max-w-[170px]" title="{{ $factura->razon_social }}">
                                    {{ $factura->razon_social }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                    {{ $factura->nit }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400 truncate max-w-[120px]">
                                    {{ $factura->sucursal?->nombre }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $factura->usuarioCreador?->name ?? 'Cajero' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-800 dark:text-white">
                                    Bs. {{ number_format((float) $factura->total, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($factura->estado === 'VALIDADA')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                            <i class="fas fa-check text-[9px]"></i> VALIDADA
                                        </span>
                                    @elseif($factura->estado === 'ANULADA')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                                            <i class="fas fa-times text-[9px]"></i> ANULADA
                                        </span>
                                    @elseif($factura->estado === 'PENDIENTE_ENVIO')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                            <i class="fas fa-clock text-[9px]"></i> CONTINGENCIA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                            {{ $factura->estado }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Ver Detalle -->
                                        <button wire:click="verDetalle({{ $factura->id }})" 
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                                title="Ver Detalle de Ítems">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>

                                        <!-- Reimprimir Ticket -->
                                        @permiso('ventas.facturas.imprimir')
                                            <button wire:click="reimprimirFactura({{ $factura->id }})" 
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                                    title="Reimprimir Ticket Fiscal">
                                                <i class="fas fa-print text-xs"></i>
                                            </button>
                                        @endpermiso

                                        <!-- Reintentar Envío a SIAT (si quedó en contingencia / pendiente) -->
                                        @if($factura->estado === 'PENDIENTE_ENVIO')
                                            <button wire:click="reintentarEnvioSiat({{ $factura->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="p-1.5 rounded-lg text-amber-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors"
                                                    title="Reintentar Envío al SIAT">
                                                <i class="fas fa-paper-plane text-xs"></i>
                                            </button>
                                        @endif

                                        <!-- Anular Factura -->
                                        @permiso('ventas.facturas.anular')
                                            @if($factura->estado !== 'ANULADA')
                                                <button wire:click="abrirModalAnulacion({{ $factura->id }})" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                                        title="Anular Factura">
                                                    <i class="fas fa-ban text-xs"></i>
                                                </button>
                                            @endif
                                        @endpermiso
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-receipt text-3xl text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-sm">No se encontraron facturas con los criterios seleccionados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación Desacoplada -->
        <div class="pt-2">
            {{ $facturas->links() }}
        </div>
    </div>

    <!-- MODAL: DETALLE DE FACTURA -->
    @if($showDetalleModal && $facturaSeleccionada)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showDetalleModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-2xl overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                            <i class="fas fa-file-invoice text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Factura N° {{ $facturaSeleccionada->numero_factura }}</h3>
                            <span class="text-[11px] text-slate-400">Emitida el {{ $facturaSeleccionada->fecha?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <button wire:click="$set('showDetalleModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Datos de Cabecera -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Cliente / Razón Social:</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ $facturaSeleccionada->razon_social }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">NIT / C.I.:</span>
                            <span class="font-mono font-bold">{{ $facturaSeleccionada->nit }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Sucursal:</span>
                            <span>{{ $facturaSeleccionada->sucursal?->nombre }}</span>
                        </div>
                        <div class="sm:col-span-3">
                            <span class="text-slate-400 block text-[11px]">Código Único de Factura (CUF):</span>
                            <span class="font-mono text-[10px] break-all text-slate-600 dark:text-slate-300">{{ $facturaSeleccionada->cuf }}</span>
                        </div>
                    </div>

                    <!-- Tabla de Ítems Vendidos -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Medicamentos Despachados</h4>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-[11px] text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800">
                                        <th class="py-2 px-3">Producto</th>
                                        <th class="py-2 px-3">Lote Físico</th>
                                        <th class="py-2 px-3 text-right">Cant.</th>
                                        <th class="py-2 px-3 text-right">Precio</th>
                                        <th class="py-2 px-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($facturaSeleccionada->detalles as $det)
                                        <tr>
                                            <td class="py-2 px-3 font-semibold text-slate-800 dark:text-white">{{ $det->nombre_producto }}</td>
                                            <td class="py-2 px-3 font-mono text-[11px]">
                                                {{ $det->numero_lote }} 
                                                <span class="text-[10px] text-slate-400">(Vence: {{ $det->fecha_vencimiento?->format('d/m/Y') }})</span>
                                            </td>
                                            <td class="py-2 px-3 text-right font-mono">{{ number_format($det->cantidad, 0) }}</td>
                                            <td class="py-2 px-3 text-right font-mono">Bs. {{ number_format($det->precio, 2) }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-bold">Bs. {{ number_format($det->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Desglose de Pagos -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Formas de Pago Registradas</h4>
                        <div class="flex flex-wrap gap-2 text-xs">
                            @foreach($facturaSeleccionada->pagos as $p)
                                <div class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center gap-2">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $p->tipo_pago }}:</span>
                                    <span class="font-mono text-teal-600 font-bold">Bs. {{ number_format($p->monto, 2) }}</span>
                                    @if($p->cambio > 0)
                                        <span class="text-[10px] text-slate-400">(Cambio: Bs. {{ number_format($p->cambio, 2) }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 flex justify-between items-center text-sm font-bold">
                        <span>Total Facturado:</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400 text-lg">Bs. {{ number_format((float) $facturaSeleccionada->total, 2) }}</span>
                    </div>

                    @if($facturaSeleccionada->estado === 'ANULADA')
                        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 space-y-1">
                            <div class="font-bold"><i class="fas fa-ban mr-1"></i> Factura Anulada el {{ $facturaSeleccionada->fecha_anulacion?->format('d/m/Y H:i') }}</div>
                            <div>Motivo: {{ $facturaSeleccionada->motivo_anulacion_descripcion }}</div>
                        </div>
                    @endif
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button wire:click="$set('showDetalleModal', false)"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: REIMPRESIÓN DE FACTURA / TICKET CON QR -->
    @if($showImpresionModal && $facturaImpresion)
        @php
            $siatServ = app(\App\Services\SiatFacturacionService::class);
            $urlQr = $siatServ->generarUrlQr($facturaImpresion);
            $literal = $siatServ->convertirMontoALetras((float) $facturaImpresion->total);
            $leyendaSin = $siatServ->obtenerLeyendaNormativa();
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showImpresionModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden text-slate-900 font-mono text-xs">
                <div class="p-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between no-print">
                    <span class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-print text-teal-600"></i> Reimpresión de Factura N° {{ $facturaImpresion->numero_factura }}
                    </span>
                    <button wire:click="$set('showImpresionModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Contenido Ticket 80mm -->
                <div class="p-5 space-y-3 bg-white" id="ticketReimpresion">
                    <div class="text-center space-y-0.5">
                        <h2 class="font-extrabold text-sm uppercase tracking-wider">{{ config('siat.razon_social') }}</h2>
                        <p class="text-[10px]">{{ $facturaImpresion->sucursal?->nombre }}</p>
                        <p class="text-[10px]">{{ $facturaImpresion->sucursal?->direccion }}</p>
                        <p class="text-[10px]">Tel: {{ config('siat.celular') }} - {{ config('siat.municipio') }}</p>
                    </div>

                    <div class="border-t border-b border-dashed border-slate-400 py-1.5 text-[11px] text-center font-bold">
                        FACTURA N° {{ $facturaImpresion->numero_factura }}
                        @if($facturaImpresion->estado === 'ANULADA')
                            <span class="block text-rose-600 font-extrabold uppercase">*** FACTURA ANULADA ***</span>
                        @endif
                        <span class="block text-[9px] font-normal text-slate-500">CÓDIGO DE AUTORIZACIÓN (CUF):</span>
                        <span class="block text-[8px] font-mono break-all text-slate-600">{{ $facturaImpresion->cuf }}</span>
                    </div>

                    <div class="text-[11px] space-y-0.5">
                        <div><b>Fecha:</b> {{ $facturaImpresion->fecha?->format('d/m/Y H:i') }}</div>
                        <div><b>Señor(es):</b> {{ $facturaImpresion->razon_social }}</div>
                        <div><b>NIT/CI:</b> {{ $facturaImpresion->nit }}</div>
                    </div>

                    <!-- Detalle -->
                    <table class="w-full text-[10px] border-t border-dashed border-slate-400 pt-1">
                        <thead>
                            <tr class="border-b border-slate-300">
                                <th class="text-left py-1">DETALLE</th>
                                <th class="text-right py-1">CANT</th>
                                <th class="text-right py-1">SUBT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($facturaImpresion->detalles as $det)
                                <tr>
                                    <td class="py-1">
                                        {{ $det->nombre_producto }}
                                        <span class="block text-[8px] text-slate-500">Lote: {{ $det->numero_lote }}</span>
                                    </td>
                                    <td class="py-1 text-right">{{ number_format($det->cantidad, 0) }}</td>
                                    <td class="py-1 text-right">Bs. {{ number_format($det->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totales -->
                    <div class="border-t border-dashed border-slate-400 pt-1.5 text-[11px] space-y-0.5">
                        <div class="flex justify-between font-bold text-xs">
                            <span>TOTAL A PAGAR:</span>
                            <span>Bs. {{ number_format($facturaImpresion->total, 2) }}</span>
                        </div>
                        <div class="pt-1 text-[9px] uppercase font-bold text-slate-600">
                            SON: {{ $literal }}
                        </div>
                    </div>

                    <!-- Código QR Oficial generado localmente con Simple QrCode -->
                    <div class="flex flex-col items-center justify-center pt-2 space-y-1">
                        <div class="p-1 border border-slate-200 bg-white inline-block">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(95)->margin(0)->generate($urlQr) !!}
                        </div>
                        <span class="text-[8px] text-slate-400 text-center">Escanee para verificar validez en SIAT</span>
                    </div>

                    <!-- Leyendas Normativas SIN -->
                    <div class="text-[8px] text-center text-slate-500 space-y-1 pt-1 border-t border-dashed border-slate-300">
                        <p class="font-bold">"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"</p>
                        <p>{{ $leyendaSin }}</p>
                    </div>
                </div>

                <style>
                    @media print {
                        @page {
                            size: 80mm auto;
                            margin: 2mm 3mm;
                        }
                        body * {
                            visibility: hidden;
                        }
                        #ticketFiscalImpresion, #ticketFiscalImpresion * {
                            visibility: visible;
                        }
                        #ticketFiscalImpresion {
                            position: fixed;
                            left: 0;
                            top: 0;
                            width: 80mm !important;
                            max-width: 80mm !important;
                            margin: 0 !important;
                            padding: 2mm 3mm !important;
                            background: white !important;
                            color: black !important;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                </style>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between no-print">
                    <button type="button" wire:click="$set('showImpresionModal', false)"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Cerrar
                    </button>
                    <button type="button" onclick="window.abrirTicketPdf('{{ route('ventas.factura.imprimir', $facturaImpresion->id) }}')"
                            class="px-4 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm inline-flex items-center gap-1.5">
                        <i class="fas fa-print"></i> Imprimir Ticket Térmico (PDF)
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: ANULACIÓN DE FACTURA CON DEVOLUCIÓN A INVENTARIO -->
    @if($showAnulacionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showAnulacionModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <i class="fas fa-ban text-sm"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Anulación de Factura</h3>
                    </div>
                    <button wire:click="$set('showAnulacionModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="confirmarAnulacion" class="p-5 space-y-4">
                    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Esta acción anulará la factura ante el Servicio de Impuestos Nacionales y <b>reincorporará automáticamente el stock a los lotes físicos en Kardex</b>.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Motivo Oficial de Anulación (Catálogo SIN) <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="motivoAnulacionId" 
                                class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            @forelse($motivosAnulacion as $mot)
                                <option value="{{ $mot->codigo_clasificador }}">{{ $mot->codigo_clasificador }} - {{ $mot->descripcion }}</option>
                            @empty
                                <option value="1">1 - FACTURA MAL EMITIDA</option>
                                <option value="2">2 - DATOS DE EMISIÓN INCORRECTOS</option>
                                <option value="3">3 - FACTURA O NOTA FISCAL DEVUELTA</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Justificación / Detalle de la Anulación <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="motivoAnulacionTexto" rows="3" placeholder="Explique brevemente el motivo..."
                                  class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                        @error('motivoAnulacionTexto') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showAnulacionModal', false)"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmarAnulacion">Confirmar y Devolver Stock</span>
                            <span wire:loading wire:target="confirmarAnulacion"><i class="fas fa-spinner fa-spin mr-1"></i> Anulando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
