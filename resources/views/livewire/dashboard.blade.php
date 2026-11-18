<div>
    <x-slot name="header">
        {{ __('Panel de Control General') }}
    </x-slot>

    <div class="space-y-6">
        <!-- Banner de Bienvenida y Filtro de Sucursal -->
        <div class="relative overflow-hidden rounded-2xl bg-slate-900 dark:bg-slate-950 text-white p-5 sm:p-7 shadow-sm border border-slate-800">
            <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-500/30 text-teal-300 text-xs font-semibold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Estación Operativa Activa
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Hola, {{ Auth::user()->name }} 👋
                    </h1>
                    <p class="text-slate-300 text-sm mt-1 max-w-xl">
                        Bienvenido a tu panel general de control <strong class="text-white font-semibold">Farma ERP</strong>. Métricas en tiempo real de inventarios, dispensación y facturación SIAT.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0 w-full sm:w-auto">
                    <!-- Selector de Sucursal para Filtrado Contextual -->
                    <div class="flex items-center gap-2 bg-slate-800/80 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-slate-300">
                        <i class="fas fa-store text-teal-400"></i>
                        <span class="font-bold text-slate-400">Sucursal:</span>
                        <select wire:model.live="filtroSucursalId" class="bg-transparent border-0 text-white font-semibold text-xs focus:ring-0 cursor-pointer p-0 pr-6">
                            <option value="" class="bg-slate-900 text-white">Todas las Sucursales</option>
                            @foreach ($sucursales as $suc)
                                <option value="{{ $suc->id }}" class="bg-slate-900 text-white">
                                    Suc. {{ $suc->codigo_sucursal }} - {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botón Acceso Rápido POS -->
                    <a href="{{ route('ventas.pos') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all duration-150">
                        <i class="fas fa-cash-register text-xs"></i>
                        <span>Abrir POS</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Fila de Tarjetas de Resumen (AdminLTE Info Boxes con Datos Reales) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Tarjeta 1: Ventas del Día -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ventas de Hoy</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1">
                            Bs. {{ number_format($totalVentasHoy, 2) }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                            @if ($porcentajeVsAyer !== null)
                                <span class="{{ $porcentajeVsAyer >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $porcentajeVsAyer >= 0 ? '+' : '' }}{{ $porcentajeVsAyer }}% vs ayer
                                </span>
                                <span class="text-slate-400">·</span>
                            @endif
                            <span class="text-slate-500 dark:text-slate-400">{{ $cantidadVentasHoy }} transacciones</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-coins text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Catálogo y Stock Bajo -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Catálogo Almacén</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1">
                            {{ number_format($totalProductos) }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                            @if ($totalProductosStockBajo > 0)
                                <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                    {{ $totalProductosStockBajo }} bajo mínimo
                                </span>
                            @else
                                <span class="text-teal-600 dark:text-teal-400 flex items-center gap-1">
                                    <i class="fas fa-check-circle text-[10px]"></i>
                                    Stock en orden
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-boxes-stacked text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Lotes por Vencer & Vencidos -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Control FEFO</p>
                        <p class="text-2xl sm:text-3xl font-extrabold {{ $lotesPorVencer > 0 ? 'text-amber-500' : 'text-slate-800 dark:text-white' }} mt-1">
                            {{ $lotesPorVencer }} <span class="text-sm font-semibold text-slate-500">Lotes</span>
                        </p>
                        <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                            <span class="text-amber-600 dark:text-amber-400">Próximos 60 días</span>
                            @if ($lotesVencidos > 0)
                                <span class="text-slate-400">·</span>
                                <span class="text-red-600 dark:text-red-400 font-bold">{{ $lotesVencidos }} vencidos</span>
                            @endif
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 4: Clientes Atendidos -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Clientes Atendidos</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1">
                            {{ number_format($clientesAtendidosHoy) }}
                        </p>
                        <div class="flex items-center gap-1 mt-2 text-xs font-semibold text-sky-600 dark:text-sky-400">
                            <i class="fas fa-user-check text-[10px]"></i>
                            <span>{{ number_format($totalClientes) }} registrados en padrón</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas (Accesos Operativos Directos) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Accesos Directos y Operaciones
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @permiso('ventas.pos.ver')
                <a href="{{ route('ventas.pos') }}" wire:navigate class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-teal-50 dark:hover:bg-teal-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-teal-300 dark:hover:border-teal-800 text-slate-700 dark:text-slate-300 hover:text-teal-700 dark:hover:text-teal-300 transition-all group font-semibold text-xs text-center">
                    <span class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-cash-register text-base"></i>
                    </span>
                    <span>Punto de Venta POS</span>
                </a>
                @endpermiso

                @permiso('inventario.productos.ver')
                <a href="{{ route('inventario.productos') }}" wire:navigate class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-emerald-300 dark:hover:border-emerald-800 text-slate-700 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-300 transition-all group font-semibold text-xs text-center">
                    <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-pills text-base"></i>
                    </span>
                    <span>Catálogo Medicamentos</span>
                </a>
                @endpermiso

                @permiso('inventario.lotes.ver')
                <a href="{{ route('inventario.lotes') }}" wire:navigate class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-amber-300 dark:hover:border-amber-800 text-slate-700 dark:text-slate-300 hover:text-amber-700 dark:hover:text-amber-300 transition-all group font-semibold text-xs text-center">
                    <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-tags text-base"></i>
                    </span>
                    <span>Lotes & Caducidades</span>
                </a>
                @endpermiso

                @permiso('inventario.kardex.ver')
                <a href="{{ route('inventario.kardex') }}" wire:navigate class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-sky-50 dark:hover:bg-sky-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-sky-300 dark:hover:border-sky-800 text-slate-700 dark:text-slate-300 hover:text-sky-700 dark:hover:text-sky-300 transition-all group font-semibold text-xs text-center">
                    <span class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-clipboard-list text-base"></i>
                    </span>
                    <span>Kardex Físico</span>
                </a>
                @endpermiso

                @permiso('ventas.facturas.ver')
                <a href="{{ route('ventas.historial') }}" wire:navigate class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-indigo-300 dark:hover:border-indigo-800 text-slate-700 dark:text-slate-300 hover:text-indigo-700 dark:hover:text-indigo-300 transition-all group font-semibold text-xs text-center">
                    <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-invoice text-base"></i>
                    </span>
                    <span>Historial Ventas SIAT</span>
                </a>
                @endpermiso

                @permiso('cajas.control.ver')
                <a href="{{ route('cajas.control') }}" wire:navigate class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-teal-50 dark:hover:bg-teal-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-teal-300 dark:hover:border-teal-800 text-slate-700 dark:text-slate-300 hover:text-teal-700 dark:hover:text-teal-300 transition-all group font-semibold text-xs text-center">
                    <span class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-vault text-base"></i>
                    </span>
                    <span>Control de Cajas</span>
                </a>
                @endpermiso
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TABLA 1: PRODUCTOS CON STOCK POR DEBAJO DEL MÍNIMO (ESTILO CLÁSICO ENMARCADO) -->
        <!-- ========================================================================= -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
            <!-- Cabecera de la Tabla -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-battery-quarter text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Productos con Stock por Debajo del Mínimo</span>
                            @if ($productosStockBajo->total() > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                    {{ $productosStockBajo->total() }} en alerta
                                </span>
                            @endif
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Medicamentos cuyo stock físico disponible (lotes vigentes) es inferior o igual al mínimo registrado en catálogo.
                        </p>
                    </div>
                </div>

                @permiso('inventario.productos.ver')
                <a href="{{ route('inventario.productos') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-100 border border-teal-200 dark:border-teal-800 transition-colors">
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                    <span>Ir al Catálogo</span>
                </a>
                @endpermiso
            </div>

            <!-- Barra de Filtros y Búsqueda -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <!-- Selector de Registros por Página -->
                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                    <span>Mostrar</span>
                    <select wire:model.live="perPageStockBajo" class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <span>registros</span>
                </div>

                <!-- Buscador en Tiempo Real -->
                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchStockBajo"
                           placeholder="Buscar por nombre, código o principio..."
                           class="w-full pl-9 pr-8 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:outline-none transition-all">
                    @if ($searchStockBajo !== '')
                        <button wire:click="limpiarFiltrosStockBajo" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Contenedor Enmarcado de la Tabla -->
            <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                            <th class="py-3 px-4">Código</th>
                            <th class="py-3 px-4">Medicamento / Principio Activo</th>
                            <th class="py-3 px-4">Presentación</th>
                            <th class="py-3 px-4 text-center">Ubicación</th>
                            <th class="py-3 px-4 text-right">Stock Mínimo</th>
                            <th class="py-3 px-4 text-right">Stock Físico Vigente</th>
                            <th class="py-3 px-4 text-right">Déficit Faltante</th>
                            <th class="py-3 px-4 text-center">Nivel Alerta</th>
                            <th class="py-3 px-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                        @forelse ($productosStockBajo as $producto)
                            @php
                                $stockActual = (float) $producto->stock_actual_vigente;
                                $minimo = (float) $producto->minimo_stock;
                                $deficit = max(0, $minimo - $stockActual);
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $producto->codigo ?: 'MED-'.str_pad($producto->id, 5, '0', STR_PAD_LEFT) }}
                                    @if ($producto->codigo_barras)
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $producto->codigo_barras }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ $producto->nombre }}</div>
                                    @if ($producto->principio_activo)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 italic">
                                            {{ $producto->principio_activo }} {{ $producto->gramaje ? '· '.$producto->gramaje : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300 text-xs">
                                    {{ $producto->presentacion ?: $producto->unidad }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-mono text-xs text-slate-600 dark:text-slate-400">
                                        {{ $producto->ubicacion_estante ?: 'S/N' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-700 dark:text-slate-300">
                                    {{ number_format($minimo, 0) }}
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold {{ $stockActual <= 0 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ number_format($stockActual, 0) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-red-600 dark:text-red-400">
                                    -{{ number_format($deficit, 0) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($stockActual <= 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 border border-red-300 dark:border-red-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                                            Agotado (0)
                                        </span>
                                    @elseif ($stockActual < ($minimo * 0.5))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400 border border-red-200 dark:border-red-900">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            Crítico (&lt;50%)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Bajo Mínimo
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('inventario.productos') }}" wire:navigate class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-2xs transition-colors" title="Gestionar y Reabastecer">
                                        <i class="fas fa-plus-circle text-xs"></i>
                                        <span>Lote</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 px-4 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <i class="fas fa-check-double text-lg"></i>
                                        </div>
                                        <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">¡Inventario en Estado Óptimo!</p>
                                        <p class="text-xs text-slate-400 max-w-sm">
                                            No se encontraron medicamentos con stock por debajo del mínimo registrado para los filtros aplicados.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación Clásica -->
            @if ($productosStockBajo->hasPages())
                <div class="pt-2">
                    {{ $productosStockBajo->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- TABLA 2: PRODUCTOS MÁS VENDIDOS (TOP ROTACIÓN EN MOSTRADOR) -->
        <!-- ========================================================================= -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
            <!-- Cabecera de la Tabla -->
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Productos Más Vendidos</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                                Top Demanda
                            </span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Ranking de medicamentos con mayor salida y recaudación según ventas válidas emitidas en el POS.
                        </p>
                    </div>
                </div>

                <!-- Tabs de Filtro de Período -->
                <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-xs font-semibold">
                    <button wire:click="$set('filtroPeriodo', 'hoy')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $filtroPeriodo === 'hoy' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Hoy
                    </button>
                    <button wire:click="$set('filtroPeriodo', 'semana')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $filtroPeriodo === 'semana' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Esta Semana
                    </button>
                    <button wire:click="$set('filtroPeriodo', 'mes')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $filtroPeriodo === 'mes' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Este Mes
                    </button>
                    <button wire:click="$set('filtroPeriodo', 'todos')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $filtroPeriodo === 'todos' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Histórico Total
                    </button>
                </div>
            </div>

            <!-- Barra de Filtros y Búsqueda -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <!-- Selector de Registros por Página -->
                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                    <span>Mostrar</span>
                    <select wire:model.live="perPageMasVendidos" class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <span>registros</span>
                </div>

                <!-- Buscador en Tiempo Real -->
                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchMasVendidos"
                           placeholder="Buscar producto en el ranking..."
                           class="w-full pl-9 pr-8 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:outline-none transition-all">
                    @if ($searchMasVendidos !== '')
                        <button wire:click="limpiarFiltrosMasVendidos" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Contenedor Enmarcado de la Tabla -->
            <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                            <th class="py-3 px-4 text-center">Rank</th>
                            <th class="py-3 px-4">Medicamento / Producto</th>
                            <th class="py-3 px-4 text-center">Unidades Dispensadas</th>
                            <th class="py-3 px-4 text-center">Tickets / Ventas</th>
                            <th class="py-3 px-4 text-right">Precio Vigente</th>
                            <th class="py-3 px-4 text-right">Total Recaudado</th>
                            <th class="py-3 px-4 text-right">Stock Físico Actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                        @forelse ($productosMasVendidos as $index => $item)
                            @php
                                $ranking = ($productosMasVendidos->currentPage() - 1) * $productosMasVendidos->perPage() + $index + 1;
                                $producto = $item->producto;
                                $stockDisponible = $producto
                                    ? ($filtroSucursalId ? $producto->stockEnSucursal($filtroSucursalId) : $producto->stockTotal())
                                    : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <!-- Ranking -->
                                <td class="py-3 px-4 text-center">
                                    @if ($ranking === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400/20 text-amber-600 dark:text-amber-300 font-extrabold text-xs shadow-2xs border border-amber-400/40" title="1er Lugar">
                                            🥇 1
                                        </span>
                                    @elseif ($ranking === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300/30 text-slate-700 dark:text-slate-200 font-extrabold text-xs shadow-2xs border border-slate-400/40" title="2do Lugar">
                                            🥈 2
                                        </span>
                                    @elseif ($ranking === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600/20 text-amber-700 dark:text-amber-400 font-extrabold text-xs shadow-2xs border border-amber-600/40" title="3er Lugar">
                                            🥉 3
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-500 dark:text-slate-400">
                                            #{{ $ranking }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Medicamento / Producto -->
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">
                                        {{ $producto->nombre ?? $item->nombre_producto_historico }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <span class="font-mono">{{ $producto->codigo ?? 'MED-'.str_pad($item->producto_id, 5, '0', STR_PAD_LEFT) }}</span>
                                        @if ($producto && $producto->presentacion)
                                            <span>·</span>
                                            <span>{{ $producto->presentacion }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Unidades Dispensadas -->
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        <i class="fas fa-check text-[10px]"></i>
                                        {{ number_format($item->total_unidades_vendidas, 0) }} uds.
                                    </span>
                                </td>

                                <!-- N° Transacciones / Tickets -->
                                <td class="py-3 px-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ number_format($item->total_transacciones) }}
                                </td>

                                <!-- Precio Vigente -->
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-300">
                                    Bs. {{ number_format((float) ($producto->precio_venta ?? 0), 2) }}
                                </td>

                                <!-- Total Recaudado -->
                                <td class="py-3 px-4 text-right font-extrabold text-teal-700 dark:text-teal-300 font-mono">
                                    Bs. {{ number_format((float) $item->total_monto_recaudado, 2) }}
                                </td>

                                <!-- Stock Físico Actual -->
                                <td class="py-3 px-4 text-right">
                                    <span class="font-bold {{ $stockDisponible <= ($producto->minimo_stock ?? 0) ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-300' }}">
                                        {{ number_format($stockDisponible, 0) }} disp.
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 px-4 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                                            <i class="fas fa-shopping-basket text-lg"></i>
                                        </div>
                                        <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">Sin Ventas Registradas</p>
                                        <p class="text-xs text-slate-400 max-w-sm">
                                            No se encontraron ventas efectuadas en el período seleccionado.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación Clásica -->
            @if ($productosMasVendidos->hasPages())
                <div class="pt-2">
                    {{ $productosMasVendidos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
