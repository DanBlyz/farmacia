<div class="space-y-4" 
     x-data="{
        focoMedicamento() { $refs.inputMed?.focus() },
        focoCliente() { $refs.inputCli?.focus() }
     }"
     @keydown.window.f2.prevent="focoMedicamento()"
     @keydown.window.f3.prevent="focoCliente()"
     @keydown.window.f4.prevent="$wire.procesarVenta()"
     @keydown.window.f8.prevent="$wire.limpiarCarrito()">

    <x-slot name="header">
        Terminal de Facturación POS
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

    <!-- ALERTA SI NO HAY CAJA ABIERTA -->
    @if(! $caja)
        <div class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm animate-fade-in">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">Turno de Caja Requerido para Vender</h3>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        No cuenta con una caja aperturada en este momento. Debe registrar su fondo inicial para habilitar la facturación.
                    </p>
                </div>
            </div>
            <a href="{{ route('cajas.control') }}" 
               class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm transition-all shrink-0">
                <i class="fas fa-cash-register mr-1.5"></i>
                Ir a Aperturar Caja
            </a>
        </div>
    @endif

    <!-- Barra de Estado Superior -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm text-xs">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $caja ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-bold' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-bold' }}">
                <span class="w-2 h-2 rounded-full {{ $caja ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                {{ $caja ? 'CAJA ACTIVA #'.$caja->id : 'CAJA CERRADA' }}
            </span>
            <span class="text-slate-500 dark:text-slate-400 hidden sm:inline">
                Sucursal: <b class="text-slate-700 dark:text-white">{{ $caja?->sucursal?->nombre ?? 'Sin asignar' }}</b>
            </span>
            <span class="text-slate-500 dark:text-slate-400 hidden md:inline">
                Punto Venta: <b class="text-slate-700 dark:text-white">{{ $caja?->puntoVenta?->codigo ?? 0 }}</b>
            </span>
        </div>

        <!-- Atajos de Teclado -->
        <div class="flex items-center gap-2 text-[11px] text-slate-400">
            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono font-bold text-slate-600 dark:text-slate-300">F2</span> Medicamento
            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono font-bold text-slate-600 dark:text-slate-300">F3</span> Cliente
            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono font-bold text-slate-600 dark:text-slate-300">F4</span> Cobrar
            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono font-bold text-slate-600 dark:text-slate-300">F8</span> Limpiar
        </div>
    </div>

    <!-- Layout Principal: 2 Columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- COLUMNA IZQUIERDA: MEDICAMENTOS Y CARRITO (8 Columnas) -->
        <div class="lg:col-span-8 space-y-4">
            
            <!-- 1. Buscador Predictivo de Medicamentos -->
            <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        <i class="fas fa-barcode text-teal-500 mr-1"></i> Búsqueda de Medicamento o Producto (F2)
                    </label>
                    <span class="text-[11px] text-slate-400">Escriba nombre, código de barras o principio activo</span>
                </div>

                <div class="relative">
                    <input type="text" 
                           x-ref="inputMed"
                           wire:model.live.debounce.300ms="productoSearch"
                           {{ ! $caja ? 'disabled' : '' }}
                           placeholder="Ej: Paracetamol, Amoxicilina, MED-001..."
                           class="w-full py-2.5 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                </div>

                <!-- Desplegable Reactivo de Resultados de Medicamentos -->
                @if(strlen(trim($productoSearch)) >= 2)
                    <div class="absolute left-4 right-4 z-30 mt-1 max-h-64 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($productosEncontrados as $prod)
                            @php
                                $stockTotal = $prod->lotes->sum('stock_actual');
                                $primerLote = $prod->lotes->first();
                            @endphp
                            <button type="button" 
                                    wire:click="seleccionarProducto({{ $prod->id }})"
                                    class="w-full p-3 text-left hover:bg-teal-50/60 dark:hover:bg-slate-700/60 transition-colors flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-xs text-slate-800 dark:text-white flex items-center gap-2">
                                        {{ $prod->nombre }}
                                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                            {{ $prod->codigo }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $prod->principio_activo }} | {{ $prod->presentacion }} | {{ $prod->gramaje }}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-mono font-bold text-xs text-teal-600 dark:text-teal-400">
                                        Bs. {{ number_format((float) $prod->precio_venta, 2) }}
                                    </div>
                                    <div class="text-[11px] font-medium {{ $stockTotal > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                        {{ $stockTotal > 0 ? 'Stock: '.number_format($stockTotal, 0) : 'Sin Stock' }}
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-400">
                                <i class="fas fa-exclamation-circle mr-1"></i> No se encontraron medicamentos con existencia para "{{ $productoSearch }}".
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- 2. Panel de Configuración del Ítem Seleccionado (Lote FEFO, Cantidad, Descuento) -->
            @if($selectedProductoId)
                @php
                    $prodSeleccionado = \App\Models\Producto::find($selectedProductoId);
                @endphp
                <div class="p-4 bg-teal-50/40 dark:bg-teal-950/20 rounded-2xl border border-teal-200 dark:border-teal-800/60 shadow-sm animate-fade-in space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-teal-600 text-white text-xs">
                                <i class="fas fa-capsules"></i>
                            </span>
                            <span class="font-bold text-xs text-slate-800 dark:text-white">
                                {{ $prodSeleccionado?->nombre }}
                            </span>
                            <span class="font-mono font-bold text-xs text-teal-700 dark:text-teal-300 bg-teal-100/70 dark:bg-teal-900/40 px-2 py-0.5 rounded-lg border border-teal-200 dark:border-teal-800">
                                Precio Venta: Bs. {{ number_format((float) ($prodSeleccionado?->precio_venta ?? 0), 2) }}
                            </span>
                        </div>
                        <button type="button" wire:click="cancelarSeleccionProducto" class="text-xs text-slate-400 hover:text-rose-500">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Selector de Lote FEFO -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Lote Sanitario (FEFO) <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model.live="selectedLoteId" 
                                    class="w-full py-1.5 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-mono">
                                <option value="AUTO_FEFO">Despacho Automático FEFO (Total Disp: {{ number_format($lotesProducto->sum('stock_actual'), 0) }} und)</option>
                                @foreach($lotesProducto as $loteOpt)
                                    <option value="{{ $loteOpt->id }}">
                                        Lote: {{ $loteOpt->numero_lote }} | Vence: {{ \Carbon\Carbon::parse($loteOpt->fecha_vencimiento)->format('d/m/Y') }} | Disp: {{ number_format($loteOpt->stock_actual, 0) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedLoteId') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Cantidad a Despachar <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="1" min="1" wire:model="cantidadAgregar"
                                   class="w-full py-1.5 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            @error('cantidadAgregar') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descuento por Ítem -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Descuento Ítem (Bs.)
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" step="0.1" min="0" wire:model="descuentoItemAgregar" placeholder="0.00"
                                       class="w-full py-1.5 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                                <button type="button" wire:click="agregarAlCarrito"
                                        class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition-all shrink-0">
                                    <i class="fas fa-cart-plus mr-1"></i> Agregar
                                </button>
                            </div>
                            @error('descuentoItemAgregar') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3. Tabla Enmarcada del Carrito de Ventas -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800/80 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-shopping-basket text-teal-500"></i>
                        Medicamentos en Carrito ({{ count($carrito) }} ítems)
                    </h3>
                    @if(count($carrito) > 0)
                        <button type="button" wire:click="limpiarCarrito" class="text-xs text-rose-500 hover:text-rose-700 font-semibold">
                            <i class="fas fa-trash-alt mr-1"></i> Vaciar Carrito (F8)
                        </button>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Producto / Medicamento</th>
                                    <th class="py-2.5 px-3">Lote / Vencimiento</th>
                                    <th class="py-2.5 px-3 text-right">P. Unit.</th>
                                    <th class="py-2.5 px-3 text-center">Cantidad</th>
                                    <th class="py-2.5 px-3 text-right">Desc.</th>
                                    <th class="py-2.5 px-3 text-right">Total</th>
                                    <th class="py-2.5 px-3 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                                @forelse($carrito as $index => $item)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-white">
                                            {{ $item['nombre'] }}
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $item['codigo'] }}</span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-mono text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                                {{ $item['numero_lote'] }}
                                            </span>
                                            <span class="block text-[10px] text-emerald-600 dark:text-emerald-400">
                                                Vence: {{ $item['fecha_vencimiento'] }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono">
                                            Bs. {{ number_format($item['precio'], 2) }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <div class="inline-flex items-center gap-1.5 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-0.5 bg-white dark:bg-slate-800">
                                                <button type="button" wire:click="modificarCantidad({{ $index }}, -1)" class="text-slate-400 hover:text-slate-700 px-1 font-bold">-</button>
                                                <span class="font-mono font-bold text-xs px-1">{{ $item['cantidad'] }}</span>
                                                <button type="button" wire:click="modificarCantidad({{ $index }}, 1)" class="text-slate-400 hover:text-slate-700 px-1 font-bold">+</button>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono text-amber-600 dark:text-amber-400">
                                            {{ $item['descuento'] > 0 ? '- Bs. '.number_format($item['descuento'], 2) : '-' }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                                            Bs. {{ number_format($item['total'], 2) }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <button type="button" wire:click="eliminarDelCarrito({{ $index }})" class="p-1 rounded text-slate-400 hover:text-rose-500">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-1">
                                                <i class="fas fa-shopping-cart text-2xl text-slate-300 dark:text-slate-600"></i>
                                                <span class="text-xs">No hay medicamentos en el carrito. Utilice el buscador superior (F2).</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: CLIENTE, COBRO Y FORMAS DE PAGO (4 Columnas) -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- 1. Tarjeta de Datos del Cliente -->
            <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 relative">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fas fa-user-tag text-teal-500"></i> Datos del Cliente (F3)
                    </span>
                    <button type="button" wire:click="abrirModalNuevoCliente"
                            class="text-[11px] font-bold text-teal-600 hover:text-teal-700 inline-flex items-center gap-1">
                        <i class="fas fa-user-plus text-[10px]"></i> + Nuevo
                    </button>
                </div>

                <!-- Buscador de Clientes -->
                <div class="relative">
                    <input type="text" 
                           x-ref="inputCli"
                           wire:model.live.debounce.300ms="clienteSearch"
                           placeholder="Buscar por NIT, CI o Razón Social..."
                           class="w-full py-1.5 pl-8 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>

                <!-- Desplegable Reactivo de Clientes -->
                @if(strlen(trim($clienteSearch)) >= 2)
                    <div class="absolute left-4 right-4 z-30 mt-1 max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($clientesEncontrados as $c)
                            <button type="button" 
                                    wire:click="seleccionarCliente({{ $c->id }})"
                                    class="w-full p-2.5 text-left hover:bg-teal-50/60 dark:hover:bg-slate-700/60 transition-colors flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-white block">{{ $c->razon_social ?: $c->nombres }}</span>
                                    <span class="text-[10px] text-slate-400">NIT/CI: {{ $c->nit ?: $c->cedula }}</span>
                                </div>
                                <span class="text-[10px] text-teal-600 font-bold">Elegir</span>
                            </button>
                        @empty
                            <div class="p-3 text-center text-[11px] text-slate-400">
                                Sin resultados para "{{ $clienteSearch }}".
                            </div>
                        @endforelse
                    </div>
                @endif

                <!-- Tarjeta del Cliente Seleccionado -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px]">Razón Social / Nombre:</span>
                        <span class="font-bold text-slate-800 dark:text-white truncate max-w-[170px]" title="{{ $clienteRazonSocial }}">
                            {{ $clienteRazonSocial }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px]">NIT / Cédula Identidad:</span>
                        <span class="font-mono font-bold text-teal-600 dark:text-teal-400">{{ $clienteDocumento }}</span>
                    </div>
                    @if($clienteId === 1)
                        <span class="inline-block text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                            <i class="fas fa-info-circle mr-0.5"></i> Cliente general por defecto (Consumidor S/N)
                        </span>
                    @endif
                </div>
            </div>

            <!-- 2. Resumen de Totales y Descuento General -->
            <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Subtotal Carrito:</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">Bs. {{ number_format($subtotalCarrito, 2) }}</span>
                </div>

                <div class="flex items-center justify-between gap-3 text-xs">
                    <span class="text-slate-500">Descuento Global (Bs.):</span>
                    <input type="number" step="0.01" min="0" inputmode="decimal" wire:model.live.debounce.350ms="descuentoGeneral" placeholder="0.00"
                           class="w-24 py-1 px-2 text-right rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                </div>

                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total a Pagar</span>
                        <span class="text-[11px] text-teal-600 font-medium">Sujeto a IVA Fiscal</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-teal-600 dark:text-teal-400">
                        Bs. {{ number_format($totalCarrito, 2) }}
                    </div>
                </div>
            </div>

            <!-- 3. Métodos de Pago Múltiples -->
            <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 text-xs">
                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">
                    <i class="fas fa-coins text-teal-500 mr-1"></i> Desglose de Pago (Múltiples Formas)
                </span>

                <div class="space-y-2">
                    <!-- Pago en Efectivo -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fas fa-money-bill-wave text-amber-500"></i> Efectivo:
                            </span>
                            <button type="button" wire:click="ajustarSaldoMetodo('efectivo')" title="Completar saldo restante en efectivo" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors border border-amber-200 dark:border-amber-800">
                                Restante
                            </button>
                        </div>
                        <input type="number" step="0.01" min="0" inputmode="decimal" wire:model.live.debounce.350ms="montoEfectivo"
                               class="w-28 py-1.5 px-2.5 text-right font-mono font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-teal-500/20">
                    </div>

                    <!-- Pago con QR -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fas fa-qrcode text-teal-500"></i> Pago QR:
                            </span>
                            <button type="button" wire:click="ajustarSaldoMetodo('qr')" title="Completar saldo restante en QR" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-colors border border-teal-200 dark:border-teal-800">
                                Restante
                            </button>
                        </div>
                        <input type="number" step="0.01" min="0" inputmode="decimal" wire:model.live.debounce.350ms="montoQr"
                               class="w-28 py-1.5 px-2.5 text-right font-mono font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-teal-500/20">
                    </div>

                    <!-- Pago con Transferencia -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fas fa-university text-indigo-500"></i> Transfer.:
                            </span>
                            <button type="button" wire:click="ajustarSaldoMetodo('transferencia')" title="Completar saldo restante en transferencia" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors border border-indigo-200 dark:border-indigo-800">
                                Restante
                            </button>
                        </div>
                        <input type="number" step="0.01" min="0" inputmode="decimal" wire:model.live.debounce.350ms="montoTransferencia"
                               class="w-28 py-1.5 px-2.5 text-right font-mono font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:ring-2 focus:ring-teal-500/20">
                    </div>
                </div>

                @php
                    $totFinal = round($totalCarrito, 2);
                    $pEf = round((float) $montoEfectivo, 2);
                    $pQr = round((float) $montoQr, 2);
                    $pTr = round((float) $montoTransferencia, 2);
                    $pagoTotal = round($pEf + $pQr + $pTr, 2);
                    $cambioVuelto = max(0, round($pEf - max(0, $totFinal - ($pQr + $pTr)), 2));
                    $faltaCobrar = max(0, round($totFinal - $pagoTotal, 2));
                @endphp

                <!-- Vuelto / Cambio -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Cambio / Vuelto al Cliente:</span>
                    <span class="font-mono font-black text-sm text-emerald-600 dark:text-emerald-400">
                        Bs. {{ number_format($cambioVuelto, 2) }}
                    </span>
                </div>

                @if($faltaCobrar > 0.009)
                    <div class="text-[11px] text-rose-500 font-semibold text-right">
                        Faltan cobrar Bs. {{ number_format($faltaCobrar, 2) }}
                    </div>
                @endif

                <!-- BOTÓN PRINCIPAL DE COBRO Y FACTURACIÓN (F4) -->
                <button type="button" 
                        wire:click="procesarVenta"
                        wire:loading.attr="disabled"
                        {{ ! $caja || count($carrito) === 0 || $faltaCobrar > 0.009 ? 'disabled' : '' }}
                        class="w-full py-3.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-extrabold text-sm shadow-md shadow-teal-600/20 transition-all flex items-center justify-center gap-2 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="procesarVenta">
                        <i class="fas fa-print mr-1"></i> COBRAR Y FACTURAR SIAT (F4)
                    </span>
                    <span wire:loading wire:target="procesarVenta">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Facturando en SIAT...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: REGISTRO RÁPIDO DE NUEVO CLIENTE -->
    @if($showNuevoClienteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showNuevoClienteModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                            <i class="fas fa-user-plus text-sm"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Registro Rápido de Cliente</h3>
                    </div>
                    <button wire:click="$set('showNuevoClienteModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarNuevoCliente" class="p-5 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nombres <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nuevoClienteNombres" placeholder="Ej: Carlos"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('nuevoClienteNombres') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Apellidos
                        </label>
                        <input type="text" wire:model="nuevoClienteApellidos" placeholder="Ej: Mendoza"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                NIT / Cédula Identidad <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="nuevoClienteDocumento" placeholder="Ej: 4892011"
                                   class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            @error('nuevoClienteDocumento') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Complemento (SEGIP)
                            </label>
                            <input type="text" wire:model="nuevoClienteComplemento" placeholder="Ej: 1A"
                                   class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Razón Social para Factura <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nuevoClienteRazonSocial" placeholder="Ej: MENDOZA CARLOS"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs uppercase text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('nuevoClienteRazonSocial') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Celular de Contacto
                        </label>
                        <input type="text" wire:model="nuevoClienteCelular" placeholder="Ej: 78945612"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showNuevoClienteModal', false)"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-all">
                            Guardar y Seleccionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: TICKET FISCAL DE FACTURA EMITIDA (VISTA DE IMPRESIÓN CON QR) -->
    @if($showTicketModal && $facturaReciente)
        @php
            $siatServ = app(\App\Services\SiatFacturacionService::class);
            $urlQr = $siatServ->generarUrlQr($facturaReciente);
            $literal = $siatServ->convertirMontoALetras((float) $facturaReciente->total);
            $leyendaSin = $siatServ->obtenerLeyendaNormativa();
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showTicketModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden text-slate-900 font-mono text-xs">
                
                <!-- Encabezado Modal -->
                <div class="p-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between no-print">
                    <span class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-teal-600"></i> Factura Emitida con Éxito
                    </span>
                    <button wire:click="$set('showTicketModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Contenido Imprimible del Ticket Rollo 80mm -->
                <div class="p-5 space-y-3 bg-white" id="ticketFiscalImpresion">
                    <div class="text-center space-y-0.5">
                        <h2 class="font-extrabold text-sm uppercase tracking-wider">{{ config('siat.razon_social', 'FARMACIA KERIGMA') }}</h2>
                        <p class="text-[10px]">{{ $facturaReciente->sucursal?->nombre }}</p>
                        <p class="text-[10px]">{{ $facturaReciente->sucursal?->direccion }}</p>
                        <p class="text-[10px]">Tel: {{ config('siat.celular', '77272241') }} - {{ config('siat.municipio', 'LA PAZ') }}</p>
                    </div>

                    <div class="border-t border-b border-dashed border-slate-400 py-1.5 text-[11px] text-center font-bold">
                        FACTURA N° {{ $facturaReciente->numero_factura }}
                        <span class="block text-[9px] font-normal text-slate-500">CÓDIGO DE AUTORIZACIÓN (CUF):</span>
                        <span class="block text-[8px] font-mono break-all text-slate-600">{{ $facturaReciente->cuf }}</span>
                    </div>

                    <div class="text-[11px] space-y-0.5">
                        <div><b>Fecha:</b> {{ $facturaReciente->fecha->format('d/m/Y H:i') }}</div>
                        <div><b>Señor(es):</b> {{ $facturaReciente->razon_social }}</div>
                        <div><b>NIT/CI:</b> {{ $facturaReciente->nit }}</div>
                    </div>

                    <!-- Detalle de Ítems -->
                    <table class="w-full text-[10px] border-t border-dashed border-slate-400 pt-1">
                        <thead>
                            <tr class="border-b border-slate-300">
                                <th class="text-left py-1">DETALLE</th>
                                <th class="text-right py-1">CANT</th>
                                <th class="text-right py-1">SUBT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($facturaReciente->detalles as $det)
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
                        @if($facturaReciente->descuento_adicional > 0)
                            <div class="flex justify-between">
                                <span>Descuento Adicional:</span>
                                <span>Bs. {{ number_format($facturaReciente->descuento_adicional, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-xs">
                            <span>TOTAL A PAGAR:</span>
                            <span>Bs. {{ number_format($facturaReciente->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Importe Base Crédito Fiscal:</span>
                            <span>Bs. {{ number_format($facturaReciente->monto_total_subjeto_iva, 2) }}</span>
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

                <!-- Botones de Acción -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between no-print">
                    <button type="button" wire:click="$set('showTicketModal', false)"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Cerrar
                    </button>
                    <button type="button" onclick="window.abrirTicketPdf('{{ route('ventas.factura.imprimir', $facturaReciente->id) }}')"
                            class="px-4 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm inline-flex items-center gap-1.5">
                        <i class="fas fa-print"></i> Imprimir Ticket Térmico (PDF)
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
