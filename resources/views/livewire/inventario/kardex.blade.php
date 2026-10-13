<div class="space-y-6">
    <x-slot name="header">
        Kardex de Movimientos
    </x-slot>
    <!-- 1. Tarjetas Métricas KPI -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Movimientos -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Movimientos</p>
                    <p class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">{{ number_format($kpi['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg">
                    <i class="fas fa-history"></i>
                </div>
            </div>
        </div>

        <!-- Total Ingresos -->
        <div class="bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-950/60 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Unidades Entradas</p>
                    <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300 mt-1">+{{ number_format($kpi['ingresos'], 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>

        <!-- Total Salidas -->
        <div class="bg-white dark:bg-slate-900 border border-amber-100 dark:border-amber-950/60 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400 uppercase tracking-wider">Unidades Salidas</p>
                    <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300 mt-1">-{{ number_format($kpi['salidas'], 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <!-- Movimientos de Hoy -->
        <div class="bg-white dark:bg-slate-900 border border-teal-100 dark:border-teal-950/60 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-teal-600 dark:text-teal-400 uppercase tracking-wider">Operaciones Hoy</p>
                    <p class="text-2xl font-extrabold text-teal-700 dark:text-teal-300 mt-1">{{ number_format($kpi['hoy']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tarjeta Contenedora Principal (Formato Enmarcado Clásico - ADR 008) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
        
        <!-- 1. Cabecera y Botón Nuevo -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                    <i class="fas fa-clipboard-list text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">
                        Kardex de Movimientos de Inventario
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Registro histórico inmutable de entradas, salidas, ventas y ajustes valorados por lote y sucursal
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('inventario.kardex.crear')
                <button type="button" 
                        wire:click="abrirModalAjuste" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="abrirModalAjuste" class="flex items-center gap-2">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Registrar Ajuste Manual</span>
                    </span>
                    <span wire:loading wire:target="abrirModalAjuste" class="flex items-center gap-2">
                        <i class="fas fa-circle-notch fa-spin text-xs"></i>
                        <span>Cargando...</span>
                    </span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra Superior de Control y Filtros (Regla 9) -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 pt-1">
            <!-- Izquierda: Paginación Dinámica -->
            <div class="flex items-center gap-2">
                <label for="perPageKardex" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                <select id="perPageKardex" 
                        wire:model.live="perPage" 
                        class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer">
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
            </div>

            <!-- Centro: Filtros Específicos -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 flex-1 max-w-2xl">
                <!-- Filtro Sucursal -->
                <div>
                    <select wire:model.live="filtroSucursalId" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="">Todas las Sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Tipo Movimiento -->
                <div>
                    <select wire:model.live="filtroTipoMovimiento" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="">Todos los Tipos</option>
                        <option value="INGRESO_INICIAL">Ingreso Inicial</option>
                        <option value="COMPRA">Compra / Ingreso</option>
                        <option value="VENTA">Venta / Factura</option>
                        <option value="AJUSTE_POSITIVO">Ajuste (+)</option>
                        <option value="AJUSTE_NEGATIVO">Ajuste (-)</option>
                        <option value="BAJA_VENCIMIENTO">Baja Vencimiento</option>
                        <option value="TRASPASO_ENTRADA">Traspaso Entrada</option>
                        <option value="TRASPASO_SALIDA">Traspaso Salida</option>
                        <option value="DEVOLUCION">Devolución</option>
                    </select>
                </div>

                <!-- Rango Fechas: Desde -->
                <div>
                    <input type="date" 
                           wire:model.live="fechaInicio" 
                           title="Fecha Desde"
                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none">
                </div>

                <!-- Rango Fechas: Hasta -->
                <div>
                    <input type="date" 
                           wire:model.live="fechaFin" 
                           title="Fecha Hasta"
                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none">
                </div>
            </div>

            <!-- Derecha: Buscador Debounce con Botón de Limpieza -->
            <div class="relative w-full xl:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar lote, producto o motivo..." 
                       class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs outline-none transition-all">
                @if ($search !== '')
                    <button type="button" 
                            wire:click="$set('search', '')" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times-circle text-xs"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- Tabla Clásica Enmarcada -->
        <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-3 px-4 text-center">Fecha & Hora</th>
                        <th class="py-3 px-4">Medicamento & Lote</th>
                        <th class="py-3 px-4">Sucursal</th>
                        <th class="py-3 px-4 text-center">Tipo Movimiento</th>
                        <th class="py-3 px-4 text-center text-emerald-600 dark:text-emerald-400">Entrada</th>
                        <th class="py-3 px-4 text-center text-amber-600 dark:text-amber-400">Salida</th>
                        <th class="py-3 px-4 text-center font-bold">Saldo Stock</th>
                        <th class="py-3 px-4">Motivo / Detalle</th>
                        <th class="py-3 px-4">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse($movimientos as $mov)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Fecha / Hora -->
                            <td class="py-3 px-4 text-center font-mono text-xs whitespace-nowrap">
                                <span class="font-bold text-slate-700 dark:text-slate-300 block">
                                    {{ $mov->fecha ? \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') : $mov->created_at->format('d/m/Y') }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">
                                    {{ $mov->fecha ? \Carbon\Carbon::parse($mov->fecha)->format('H:i') : $mov->created_at->format('H:i') }}
                                </span>
                            </td>

                            <!-- Medicamento & Lote -->
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800 dark:text-white">
                                    {{ $mov->producto->nombre ?? 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($mov->numero_lote)
                                        <span class="font-mono text-[11px] font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-1.5 py-0.5 rounded border border-teal-200 dark:border-teal-800">
                                            Lote: {{ $mov->numero_lote }}
                                        </span>
                                    @endif
                                    @if($mov->fecha_caducidad)
                                        <span class="text-[10px] text-slate-400">
                                            Vence: {{ \Carbon\Carbon::parse($mov->fecha_caducidad)->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Sucursal -->
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                <span class="font-medium text-slate-700 dark:text-slate-200 block">
                                    {{ $mov->sucursal->nombre ?? 'N/A' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    {{ $mov->sucursal->codigo_sucursal ?? '' }}
                                </span>
                            </td>

                            <!-- Tipo de Movimiento Badge -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($mov->tipo_movimiento === 'INGRESO_INICIAL')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Ingreso Inicial
                                    </span>
                                @elseif($mov->tipo_movimiento === 'COMPRA')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                                        Compra
                                    </span>
                                @elseif($mov->tipo_movimiento === 'VENTA')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        Venta POS
                                    </span>
                                @elseif($mov->tipo_movimiento === 'AJUSTE_POSITIVO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                                        Ajuste (+)
                                    </span>
                                @elseif($mov->tipo_movimiento === 'AJUSTE_NEGATIVO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        Ajuste (-)
                                    </span>
                                @elseif($mov->tipo_movimiento === 'BAJA_VENCIMIENTO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                        Baja Vencimiento
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ str_replace('_', ' ', $mov->tipo_movimiento) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Entrada (Ingreso) -->
                            <td class="py-3 px-4 text-center font-mono font-bold">
                                @if($mov->ingreso > 0)
                                    <span class="text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($mov->ingreso, 0) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                @endif
                            </td>

                            <!-- Salida -->
                            <td class="py-3 px-4 text-center font-mono font-bold">
                                @if($mov->salida > 0)
                                    <span class="text-amber-600 dark:text-amber-400">
                                        -{{ number_format($mov->salida, 0) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                @endif
                            </td>

                            <!-- Saldo Stock Resultante -->
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-slate-800 dark:text-white">
                                {{ number_format($mov->saldo_stock, 0) }}
                            </td>

                            <!-- Motivo / Descripción -->
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                {{ $mov->descripcion ?? 'Sin descripción' }}
                            </td>

                            <!-- Responsable Auditoría -->
                            <td class="py-3 px-4 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <span class="font-medium text-slate-700 dark:text-slate-300 block">
                                    {{ $mov->usuarioCreador->name ?? 'Sistema' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-clipboard-list text-3xl mb-3 block text-slate-300 dark:text-slate-600"></i>
                                <span class="text-sm font-medium">No se encontraron movimientos registrados con los filtros aplicados.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 4. Paginación Desacoplada -->
        <div class="pt-2">
            {{ $movimientos->links() }}
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL DE REGISTRO MANUAL DE AJUSTE EN KARDEX -->
    <!-- ========================================================================= -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-data @keydown.escape.window="$wire.closeModal()">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col">
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-teal-600"></i>
                    Asentar Ajuste Directo en Kardex
                </h3>
                <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Formulario Ajuste -->
            <form wire:submit="guardarAjusteKardex" class="p-6 space-y-4">
                <!-- Sucursal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Sucursal <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="nuevoSucursalId" 
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                    @error('nuevoSucursalId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Medicamento con Buscador Interactivo -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Medicamento <span class="text-rose-500">*</span>
                    </label>

                    @if($productoSeleccionado)
                        <!-- Tarjeta de Medicamento Seleccionado -->
                        <div class="flex items-center justify-between p-3 rounded-xl border border-teal-200 dark:border-teal-800/60 bg-teal-50/60 dark:bg-teal-950/20">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white flex items-center gap-2">
                                        <span>{{ $productoSeleccionado->nombre }}</span>
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 font-bold">
                                            {{ $productoSeleccionado->codigo }}
                                        </span>
                                        @if($productoSeleccionado->requiere_receta)
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold">Rx</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                                        @if($productoSeleccionado->principio_activo)
                                            <span><i class="fas fa-flask text-[9px] mr-1 text-teal-600"></i>{{ $productoSeleccionado->principio_activo }}</span>
                                        @endif
                                        @if($productoSeleccionado->proveedor)
                                            <span>• <i class="fas fa-truck text-[9px] mr-1 text-slate-400"></i>{{ $productoSeleccionado->proveedor->razon_social ?: $productoSeleccionado->proveedor->nombre }}</span>
                                        @endif
                                        @if($productoSeleccionado->presentacion)
                                            <span>• {{ $productoSeleccionado->presentacion }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button type="button" 
                                    wire:click="limpiarProducto" 
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer shrink-0"
                                    title="Cambiar medicamento">
                                <i class="fas fa-sync-alt text-[10px]"></i>
                                <span>Cambiar</span>
                            </button>
                        </div>
                    @else
                        <!-- Buscador con Entrada de Texto en Tiempo Real -->
                        <div class="relative">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input type="text" 
                                       wire:model.live.debounce.250ms="productoSearch" 
                                       placeholder="Escriba para buscar por nombre, código, barra o principio activo..." 
                                       class="w-full pl-9 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                                @if($productoSearch !== '')
                                    <button type="button" 
                                            wire:click="$set('productoSearch', '')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <i class="fas fa-times-circle text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Menú Desplegable con Resultados -->
                            @if(strlen(trim($productoSearch)) >= 1)
                                <div class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($productosBusqueda as $prod)
                                        <button type="button" 
                                                wire:click="seleccionarProducto({{ $prod->id }})" 
                                                class="w-full p-2.5 text-left hover:bg-teal-50/60 dark:hover:bg-teal-950/30 flex items-center justify-between transition-colors cursor-pointer group">
                                            <div>
                                                <div class="font-bold text-xs text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 flex items-center gap-1.5">
                                                    <span>{{ $prod->nombre }}</span>
                                                    @if($prod->requiere_receta)
                                                        <span class="text-[9px] px-1 py-0.2 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold">Rx</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                                    <span class="font-mono text-teal-600 dark:text-teal-400 font-semibold">{{ $prod->codigo }}</span>
                                                    @if($prod->principio_activo)
                                                        <span>• DCI: {{ $prod->principio_activo }}</span>
                                                    @endif
                                                    @if($prod->proveedor)
                                                        <span>• {{ $prod->proveedor->razon_social ?: $prod->proveedor->nombre }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right pl-3 shrink-0">
                                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 block">Bs. {{ number_format($prod->precio_venta, 2) }}</span>
                                                <span class="text-[10px] text-teal-600 dark:text-teal-400 font-medium group-hover:underline">Seleccionar &rarr;</span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="p-4 text-center text-xs text-slate-400">
                                            <i class="fas fa-exclamation-circle mr-1"></i> No se encontraron medicamentos que coincidan con "{{ $productoSearch }}".
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif
                    @error('nuevoProductoId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Lote Específico o Nuevo Lote -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Lote Físico Intervenido <span class="text-rose-500">*</span>
                        </label>
                        <div class="inline-flex p-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                            <button type="button" 
                                    wire:click="cambiarModoLote(false)"
                                    class="px-2.5 py-1 rounded-md font-medium transition-colors {{ !$esNuevoLote ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }}">
                                <i class="fas fa-boxes mr-1"></i> Lote Existente
                            </button>
                            <button type="button" 
                                    wire:click="cambiarModoLote(true)"
                                    class="px-2.5 py-1 rounded-md font-medium transition-colors {{ $esNuevoLote ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }}">
                                <i class="fas fa-plus-circle mr-1"></i> Nuevo Lote
                            </button>
                        </div>
                    </div>

                    @if(!$esNuevoLote)
                        <div>
                            <select wire:model="nuevoLoteId" 
                                    {{ empty($lotesDisponibles) || $lotesDisponibles->isEmpty() ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">{{ $nuevoProductoId ? ($lotesDisponibles->isEmpty() ? 'Sin lotes en esta sucursal (Utilice "Nuevo Lote")' : 'Seleccione el lote...') : 'Primero elija un medicamento' }}</option>
                                @foreach($lotesDisponibles as $lot)
                                    <option value="{{ $lot->id }}">
                                        Lote: {{ $lot->numero_lote }} | Vence: {{ \Carbon\Carbon::parse($lot->fecha_vencimiento)->format('d/m/Y') }} | Stock: {{ number_format($lot->stock_actual, 0) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nuevoLoteId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="p-3 bg-teal-50/50 dark:bg-teal-950/20 rounded-xl border border-teal-200 dark:border-teal-800/40 space-y-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Código de Lote (Editable)</span>
                                    <button type="button" 
                                            wire:click="generarCodigoNuevoLote"
                                            class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-700 inline-flex items-center gap-1 transition-colors">
                                        <i class="fas fa-sync-alt text-[10px]"></i> Autogenerar
                                    </button>
                                </div>
                                <div class="relative">
                                    <input type="text" 
                                           wire:model="nuevoNumeroLote" 
                                           placeholder="Ej: LOT-202610-00001"
                                           class="w-full py-2 pl-3 pr-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-teal-500 text-xs">
                                        <i class="fas fa-barcode"></i>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                    Código correlativo autogenerado. Puede modificarlo según el lote impreso en el empaque.
                                </p>
                                @error('nuevoNumeroLote') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha de Vencimiento <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       wire:model="nuevaFechaVencimiento"
                                       class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                                @error('nuevaFechaVencimiento') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tipo de Movimiento -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tipo de Operación <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="nuevoTipoMovimiento" 
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-medium">
                        <option value="AJUSTE_POSITIVO">➕ AJUSTE POSITIVO (Incremento / Sobrante físico)</option>
                        <option value="AJUSTE_NEGATIVO">➖ AJUSTE NEGATIVO (Decremento / Merma / Conteo)</option>
                        <option value="BAJA_VENCIMIENTO">🛑 BAJA POR VENCIMIENTO (Destrucción / Retiro)</option>
                    </select>
                </div>

                <!-- Cantidad -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Cantidad de Unidades <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" 
                           step="1" 
                           wire:model="nuevaCantidad" 
                           min="1" 
                           class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-mono font-bold">
                    @error('nuevaCantidad') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Motivo / Justificación -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo / Justificación de Auditoría <span class="text-rose-500">*</span>
                    </label>
                    <textarea wire:model="nuevoMotivo" 
                              rows="3" 
                              placeholder="Indique con claridad la causa que origina este movimiento para auditoría sanitaria y fiscal..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
                    @error('nuevoMotivo') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Footer Acciones -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium transition-all shadow-sm shadow-teal-900/20 active:scale-95 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="guardarAjusteKardex">Asentar Movimiento</span>
                        <span wire:loading wire:target="guardarAjusteKardex" class="flex items-center gap-1.5">
                            <i class="fas fa-circle-notch fa-spin text-xs"></i>
                            <span>Procesando...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
