<div class="space-y-6">
    <x-slot name="header">
        Reportes Generales y Analítica
    </x-slot>

    <!-- 1. Navegación por Pestañas (Tabs) con Colores Médicos Sólidos -->
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900/80 rounded-2xl border border-slate-200 dark:border-slate-800">
        @permiso('reportes.inventario.ver')
            <button type="button" wire:click="cambiarTab('inventario')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ $tab === 'inventario' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                <i class="fas fa-boxes-stacked text-base"></i>
                <span>Inventario y Kardex</span>
            </button>
        @endpermiso

        @permiso('reportes.utilidades.ver')
            <button type="button" wire:click="cambiarTab('utilidades')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ $tab === 'utilidades' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                <i class="fas fa-hand-holding-dollar text-base"></i>
                <span>Utilidades y Ganancias</span>
            </button>
        @endpermiso

        @permiso('reportes.cajas.ver')
            <button type="button" wire:click="cambiarTab('cajas')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ $tab === 'cajas' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                <i class="fas fa-cash-register text-base"></i>
                <span>Control de Cajas</span>
            </button>
        @endpermiso

        @permiso('reportes.clientes.ver')
            <button type="button" wire:click="cambiarTab('clientes')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ $tab === 'clientes' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-800/60' }}">
                <i class="fas fa-users-viewfinder text-base"></i>
                <span>Clientes Frecuentes</span>
            </button>
        @endpermiso
    </div>

    <!-- 2. Tarjetas KPI según Pestaña Activa -->
    @if ($tab === 'inventario')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unidades Ingresadas</span>
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-arrow-down-long text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        +{{ number_format($kpis['totalIngresos'] ?? 0, 0) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Compras y traspasos de entrada</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unidades Salientes</span>
                    <span class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <i class="fas fa-arrow-up-long text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">
                        -{{ number_format($kpis['totalSalidas'] ?? 0, 0) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Ventas y mermas del periodo</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Valuación Compra (Pág)</span>
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <i class="fas fa-tag text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                        Bs. {{ number_format($kpis['valCompraPagina'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Costo valorado en stock final</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Valuación Venta (Pág)</span>
                    <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                        <i class="fas fa-coins text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-teal-600 dark:text-teal-400">
                        Bs. {{ number_format($kpis['valVentaPagina'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Ingreso proyectado en mostrador</p>
                </div>
            </div>
        </div>
    @elseif ($tab === 'utilidades')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Facturado</span>
                    <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                        <i class="fas fa-receipt text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-800 dark:text-white">
                        Bs. {{ number_format($kpis['totalVentas'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ number_format($kpis['cantidadFacturas'] ?? 0, 0) }} facturas validadas</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo de Lote</span>
                    <span class="p-2 rounded-xl bg-slate-500/10 text-slate-600 dark:text-slate-400">
                        <i class="fas fa-boxes-packing text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-700 dark:text-slate-300">
                        Bs. {{ number_format($kpis['totalCosto'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Adquisición exacta por lote</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Utilidad Bruta</span>
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-chart-line text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        Bs. {{ number_format($kpis['totalUtilidad'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                        Margen global: {{ number_format($kpis['margenGlobal'] ?? 0, 1) }}%
                    </p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Desglose Cobros</span>
                    <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <i class="fas fa-wallet text-base"></i>
                    </span>
                </div>
                <div class="mt-2 space-y-1 text-xs">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500">Efectivo:</span>
                        <span class="font-bold text-slate-800 dark:text-white">Bs. {{ number_format($kpis['totalEfectivo'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500">QR:</span>
                        <span class="font-bold text-teal-600 dark:text-teal-400">Bs. {{ number_format($kpis['totalQr'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500">Transf.:</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">Bs. {{ number_format($kpis['totalTransferencia'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($tab === 'cajas')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Aperturas</span>
                    <span class="p-2 rounded-xl bg-slate-500/10 text-slate-600 dark:text-slate-400">
                        <i class="fas fa-door-open text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-800 dark:text-white">
                        Bs. {{ number_format($kpis['totalAperturas'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ number_format($kpis['cantidadCajas'] ?? 0, 0) }} turnos de caja</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Efectivo Cobrado</span>
                    <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                        <i class="fas fa-money-bill-wave text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-teal-600 dark:text-teal-400">
                        Bs. {{ number_format($kpis['totalEfectivo'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Cobranza líquida en gaveta</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">QR / Digital</span>
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <i class="fas fa-qrcode text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                        Bs. {{ number_format($kpis['totalQr'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Cobros verificados por QR</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Ventas Turno</span>
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-vault text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        Bs. {{ number_format($kpis['totalVentas'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Efectivo + QR + Transf.</p>
                </div>
            </div>
        </div>
    @elseif ($tab === 'clientes')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Clientes Activos</span>
                    <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                        <i class="fas fa-users text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-800 dark:text-white">
                        {{ number_format($kpis['totalClientes'] ?? 0, 0) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Con compras registradas</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Facturas</span>
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <i class="fas fa-file-invoice text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                        {{ number_format($kpis['totalFacturas'] ?? 0, 0) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Facturas emitidas con cliente</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Monto Facturado</span>
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-money-bills text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        Bs. {{ number_format($kpis['totalComprado'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Volumen total de facturación</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ticket Promedio</span>
                    <span class="p-2 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <i class="fas fa-basket-shopping text-base"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">
                        Bs. {{ number_format($kpis['ticketPromedio'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Gasto medio por compra</p>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. Barra de Filtros Unificada y Controles Clásicos -->
    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <!-- Fecha Inicio -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                    Fecha Inicio
                </label>
                <input type="date" wire:model.live="fechaInicio"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                    Fecha Fin
                </label>
                <input type="date" wire:model.live="fechaFin"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
            </div>

            <!-- Sucursal -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                    Sucursal
                </label>
                <select wire:model.live="filtroSucursalId"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Todas las Sucursales</option>
                    @foreach ($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtros Condicionales por Tab -->
            @if ($tab === 'inventario')
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                        Producto Específico
                    </label>
                    <select wire:model.live="filtroProductoId"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Todos los productos activos</option>
                        @foreach ($productosLista as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->codigo_barras ?? $p->codigo ?? 'S/C' }})</option>
                        @endforeach
                    </select>
                </div>
            @elseif ($tab === 'utilidades')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                        Cajero / Usuario
                    </label>
                    <select wire:model.live="filtroUsuarioUtilidadId"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Todos los usuarios</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                        N° de Caja
                    </label>
                    <input type="number" wire:model.live.debounce.300ms="filtroCajaUtilidadId" placeholder="Ej: 1"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
                </div>
            @elseif ($tab === 'cajas')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                        Usuario / Cajero
                    </label>
                    <select wire:model.live="filtroUsuarioCajaId"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Todos los cajeros</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                        Estado de Caja
                    </label>
                    <select wire:model.live="filtroEstadoCaja"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Todos</option>
                        <option value="ABIERTA">Abierta</option>
                        <option value="CERRADA">Cerrada</option>
                    </select>
                </div>
            @endif

            <!-- Acciones: Botón Limpiar y Botón Exportar PDF -->
            <div class="flex items-end gap-2 {{ $tab === 'clientes' ? 'lg:col-span-3' : ($tab === 'inventario' ? '' : '') }}">
                <button type="button" wire:click="limpiarFiltros"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    title="Limpiar filtros">
                    <i class="fas fa-rotate-left mr-1"></i> Limpiar
                </button>

                <a href="{{ $this->obtenerUrlPdf() }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                    <i class="fas fa-file-pdf text-sm"></i>
                    <span>Exportar PDF</span>
                </a>
            </div>
        </div>

        <!-- Controles Inferiores: Selector de registros por página y buscador por texto -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Mostrar</span>
                <select wire:model.live="perPage"
                    class="text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white py-1 px-2 focus:ring-teal-500 focus:border-teal-500">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>registros por página</span>
            </div>

            @if ($tab !== 'cajas')
                <div class="w-full sm:w-72 relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="{{ $tab === 'inventario' ? 'Buscar medicamento / código...' : ($tab === 'utilidades' ? 'Buscar factura / cliente / NIT...' : 'Buscar cliente / NIT...') }}"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white pl-9 pr-8 focus:border-teal-500 focus:ring-teal-500">
                    <span class="absolute left-3 top-2.5 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- 4. Tablas Clásicas Enmarcadas con Bordes y Semáforos Sólidos -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            @if ($tab === 'inventario')
                <!-- TABLA 1: INVENTARIO & KARDEX -->
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                            <th class="py-3 px-4 text-center">Código</th>
                            <th class="py-3 px-4">Producto / Presentación</th>
                            <th class="py-3 px-3 text-right">Stock Inicial</th>
                            <th class="py-3 px-3 text-right">Ingresos</th>
                            <th class="py-3 px-3 text-right">Salidas</th>
                            <th class="py-3 px-3 text-right font-bold">Stock Final</th>
                            <th class="py-3 px-3 text-right">P. Compra</th>
                            <th class="py-3 px-3 text-right">P. Venta</th>
                            <th class="py-3 px-3 text-right font-bold">Val. Compra</th>
                            <th class="py-3 px-4 text-right font-bold text-teal-600 dark:text-teal-400">Val. Venta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($items as $row)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 text-center font-mono text-slate-500">
                                    {{ $row['producto']->codigo_barras ?? $row['producto']->codigo ?? 'S/C' }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $row['producto']->nombre }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $row['producto']->presentacion ?? $row['producto']->unidad ?? 'Unidad' }}
                                        @if($row['producto']->principio_activo)
                                            &bull; {{ $row['producto']->principio_activo }}
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono">{{ number_format($row['stock_inicial'], 0) }}</td>
                                <td class="py-3 px-3 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                    +{{ number_format($row['ingresos'], 0) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-semibold text-rose-600 dark:text-rose-400">
                                    -{{ number_format($row['salidas'], 0) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-extrabold text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800/30">
                                    {{ number_format($row['stock_final'], 0) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono">Bs. {{ number_format($row['precio_compra'], 2) }}</td>
                                <td class="py-3 px-3 text-right font-mono">Bs. {{ number_format($row['precio_venta'], 2) }}</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    Bs. {{ number_format($row['valor_compra'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-teal-600 dark:text-teal-400">
                                    Bs. {{ number_format($row['valor_venta'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-boxes text-3xl mb-2 text-slate-300"></i>
                                    <div>No se encontraron registros de inventario para el periodo y filtros indicados.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($tab === 'utilidades')
                <!-- TABLA 2: UTILIDADES & FACTURACIÓN -->
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                            <th class="py-3 px-4 text-center">N° Factura</th>
                            <th class="py-3 px-4">Fecha / Hora</th>
                            <th class="py-3 px-4">Cajero / Usuario</th>
                            <th class="py-3 px-3 text-center">Caja</th>
                            <th class="py-3 px-4">Cliente (NIT)</th>
                            <th class="py-3 px-3 text-right font-bold">Venta Total</th>
                            <th class="py-3 px-3 text-right">Costo Lote</th>
                            <th class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Utilidad</th>
                            <th class="py-3 px-3 text-center">Margen</th>
                            <th class="py-3 px-3 text-right">Efectivo</th>
                            <th class="py-3 px-4 text-right">QR / Transf.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($items as $row)
                            @php $f = $row['factura']; @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ $f->numero_factura }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500">
                                    {{ \Carbon\Carbon::parse($f->fecha)->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                    {{ $f->usuarioCreador->name ?? 'Usuario Sistema' }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono">
                                    @if($f->caja_id)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                            C#{{ $f->caja_id }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">--</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900 dark:text-white truncate max-w-[180px]">{{ $f->razon_social }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIT: {{ $f->nit }}</div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-extrabold text-slate-900 dark:text-white">
                                    Bs. {{ number_format($f->total, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-500">
                                    Bs. {{ number_format($row['costo'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-extrabold {{ $row['utilidad'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                    Bs. {{ number_format($row['utilidad'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold {{ $row['margen'] >= 20 ? 'text-emerald-600' : 'text-amber-500' }}">
                                    {{ number_format($row['margen'], 1) }}%
                                </td>
                                <td class="py-3 px-3 text-right font-mono">
                                    Bs. {{ number_format($row['efectivo'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono">
                                    Bs. {{ number_format($row['qr'] + $row['transferencia'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-file-invoice-dollar text-3xl mb-2 text-slate-300"></i>
                                    <div>No se registraron facturas validadas en el rango de fechas seleccionado.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($tab === 'cajas')
                <!-- TABLA 3: CONTROL DE CAJAS & ARQUEOS -->
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                            <th class="py-3 px-4 text-center">Caja #</th>
                            <th class="py-3 px-4">Cajero / Usuario</th>
                            <th class="py-3 px-3 text-center">Apertura</th>
                            <th class="py-3 px-3 text-center">Cierre</th>
                            <th class="py-3 px-3 text-center">Estado</th>
                            <th class="py-3 px-3 text-right">Monto Apertura</th>
                            <th class="py-3 px-3 text-right font-bold text-teal-600 dark:text-teal-400">Efectivo Cobrado</th>
                            <th class="py-3 px-3 text-right">QR</th>
                            <th class="py-3 px-3 text-right">Transferencia</th>
                            <th class="py-3 px-3 text-right font-bold">Monto Cierre</th>
                            <th class="py-3 px-4 text-right font-bold">Cuadre / Dif.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($items as $row)
                            @php $c = $row['caja']; @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ $c->id }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $c->usuario->name ?? 'Usuario Desconocido' }}</div>
                                    @if($c->puntoVenta)
                                        <div class="text-[11px] text-slate-400">POS: {{ $c->puntoVenta->nombre }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-slate-500">
                                    {{ \Carbon\Carbon::parse($c->fecha_apertura)->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-slate-500">
                                    {{ $c->fecha_cierre ? \Carbon\Carbon::parse($c->fecha_cierre)->format('d/m/Y H:i') : '--' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($c->estado === 'ABIERTA')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                            ABIERTA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                                            CERRADA
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-mono">Bs. {{ number_format($row['monto_apertura'], 2) }}</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-teal-600 dark:text-teal-400">
                                    Bs. {{ number_format($row['efectivo'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono">Bs. {{ number_format($row['qr'], 2) }}</td>
                                <td class="py-3 px-3 text-right font-mono">Bs. {{ number_format($row['transferencia'], 2) }}</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $row['monto_cierre'] !== null ? 'Bs. ' . number_format($row['monto_cierre'], 2) : 'Sin Cerrar' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold">
                                    @if($row['diferencia'] !== null)
                                        @if(abs($row['diferencia']) < 0.01)
                                            <span class="text-emerald-600 dark:text-emerald-400">Bs. 0.00 (OK)</span>
                                        @elseif($row['diferencia'] > 0)
                                            <span class="text-blue-600 dark:text-blue-400">+Bs. {{ number_format($row['diferencia'], 2) }} (Sob.)</span>
                                        @else
                                            <span class="text-rose-600 dark:text-rose-400">-Bs. {{ number_format(abs($row['diferencia']), 2) }} (Falt.)</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">--</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-cash-register text-3xl mb-2 text-slate-300"></i>
                                    <div>No se encontraron turnos de caja para los filtros seleccionados.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($tab === 'clientes')
                <!-- TABLA 4: CLIENTES FRECUENTES -->
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                            <th class="py-3 px-4 text-center">Rank</th>
                            <th class="py-3 px-4">Razón Social / Nombre del Cliente</th>
                            <th class="py-3 px-4">NIT / Cédula</th>
                            <th class="py-3 px-3 text-center font-bold">N° Facturas</th>
                            <th class="py-3 px-4 text-right font-bold text-teal-600 dark:text-teal-400">Total Comprado</th>
                            <th class="py-3 px-4 text-right">Ticket Promedio</th>
                            <th class="py-3 px-4 text-center">Última Compra</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($items as $index => $row)
                            @php
                                $rankingNum = ($items->currentPage() - 1) * $items->perPage() + $index + 1;
                                $rankStyle = match($rankingNum) {
                                    1 => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950 dark:text-amber-300',
                                    2 => 'bg-slate-200 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
                                    3 => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-950 dark:text-orange-300',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800/60 dark:text-slate-400',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold border {{ $rankStyle }}">
                                        {{ $rankingNum }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $row['nombre_cliente'] }}</div>
                                    @if($row['modelo']?->telefono)
                                        <div class="text-[11px] text-slate-400">
                                            <i class="fas fa-phone text-[9px] mr-0.5"></i> {{ $row['modelo']->telefono }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    {{ $row['nit_cliente'] }}
                                    @if($row['modelo']?->complemento)
                                        <span class="text-teal-600 dark:text-teal-400 font-bold">-{{ $row['modelo']->complemento }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $row['total_facturas'] }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-teal-600 dark:text-teal-400">
                                    Bs. {{ number_format($row['total_comprado'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold">
                                    Bs. {{ number_format($row['ticket_promedio'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-slate-500">
                                    {{ \Carbon\Carbon::parse($row['ultima_compra'])->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-users-slash text-3xl mb-2 text-slate-300"></i>
                                    <div>No se encontraron registros de ventas a clientes en el rango de fechas seleccionado.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <!-- 5. Paginación Clásica con Resultados -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500">
                Mostrando <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $items->firstItem() ?? 0 }}</span>
                a <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $items->lastItem() ?? 0 }}</span>
                de <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $items->total() }}</span> registros
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>