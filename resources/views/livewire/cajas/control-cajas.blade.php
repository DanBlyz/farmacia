<div class="space-y-6">
    <x-slot name="header">
        Apertura y Cierre de Caja
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

    <!-- 1. Tarjetas KPI de Estado de Caja Actual -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Estado de Caja -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mi Turno Actual</span>
                <span class="p-2 rounded-xl {{ $cajaActiva ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                    <i class="fas {{ $cajaActiva ? 'fa-cash-register' : 'fa-lock' }} text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $cajaActiva ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <span class="text-lg font-bold text-slate-800 dark:text-white">
                        {{ $cajaActiva ? 'CAJA ABIERTA' : 'SIN CAJA ABIERTA' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1 truncate">
                    @if($cajaActiva)
                        {{ $cajaActiva->sucursal?->nombre }} | PV {{ $cajaActiva->puntoVenta?->codigo }} ({{ $cajaActiva->fecha_apertura->format('H:i d/m') }})
                    @else
                        Debe abrir caja para vender en el POS
                    @endif
                </p>
            </div>
        </div>

        <!-- Fondo Inicial Apertura -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fondo Inicial</span>
                <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <i class="fas fa-coins text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-800 dark:text-white">
                    Bs. {{ number_format((float) ($cajaActiva?->monto_apertura ?? 0), 2) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Sencillo / cambio registrado</p>
            </div>
        </div>

        <!-- Ventas en Efectivo -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ventas Efectivo</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <i class="fas fa-money-bill-wave text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                    Bs. {{ number_format((float) ($cajaActiva?->venta_contado ?? 0), 2) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    QR: Bs. {{ number_format((float) ($cajaActiva?->total_qr ?? 0), 2) }} | Transf: Bs. {{ number_format((float) ($cajaActiva?->total_transferencia ?? 0), 2) }}
                </p>
            </div>
        </div>

        <!-- Total Esperado en Efectivo -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Efectivo en Caja</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-wallet text-base"></i>
                </span>
            </div>
            <div class="mt-3">
                @php
                    $efectivoEnCaja = (float) ($cajaActiva?->monto_apertura ?? 0) 
                        + (float) ($cajaActiva?->venta_contado ?? 0) 
                        + (float) ($cajaActiva?->otro_ingreso ?? 0) 
                        - (float) ($cajaActiva?->total_salida ?? 0);
                @endphp
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                    Bs. {{ number_format($efectivoEnCaja, 2) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Total esperado físico a entregar
                </p>
            </div>
        </div>
    </div>

    <!-- 2. Botonera de Acciones de Caja -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-2">
            @if(! $cajaActiva)
                @permiso('cajas.control.abrir')
                    <button wire:click="abrirModalApertura"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none">
                        <i class="fas fa-key text-xs"></i>
                        Aperturar Turno de Caja
                    </button>
                @endpermiso
            @else
                @permiso('cajas.control.cerrar')
                    <button wire:click="abrirModalCierre"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none">
                        <i class="fas fa-calculator text-xs"></i>
                        Arqueo y Cierre de Caja
                    </button>
                @endpermiso

                @permiso('cajas.control.movimientos')
                    <button wire:click="abrirModalMovimiento"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all focus:outline-none">
                        <i class="fas fa-exchange-alt text-xs text-teal-500"></i>
                        Ingreso / Egreso Menor
                    </button>
                @endpermiso

                <a href="{{ route('ventas.pos') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none">
                    <i class="fas fa-shopping-cart text-xs"></i>
                    Ir a Terminal POS (Ventas)
                </a>
            @endif
        </div>

        <div class="text-xs text-slate-500 font-medium">
            <i class="fas fa-info-circle mr-1 text-teal-500"></i>
            {{ $cajaActiva ? 'Caja activa para facturar en '.$cajaActiva->sucursal?->nombre : 'Se requiere caja abierta para emitir ventas' }}
        </div>
    </div>

    <!-- 3. Tabla Clásica Enmarcada: Historial de Cajas -->
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
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
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
                    <option value="ABIERTA">Abiertas</option>
                    <option value="CERRADA">Cerradas</option>
                </select>

                <!-- Fechas -->
                <input type="date" wire:model.live="fechaInicio" title="Fecha Inicio"
                       class="py-1.5 px-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300">
                <input type="date" wire:model.live="fechaFin" title="Fecha Fin"
                       class="py-1.5 px-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300">
            </div>

            <!-- Derecha: Buscador con Botón Limpiar -->
            <div class="relative w-full md:w-64">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por usuario / detalle..."
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
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Cajero / Usuario</th>
                            <th class="py-3 px-4">Sucursal / PV</th>
                            <th class="py-3 px-4">Apertura</th>
                            <th class="py-3 px-4">Cierre</th>
                            <th class="py-3 px-4 text-right">Fondo Ini.</th>
                            <th class="py-3 px-4 text-right">Ventas Efect.</th>
                            <th class="py-3 px-4 text-right">QR / Transf.</th>
                            <th class="py-3 px-4 text-right">Total Entregado</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                        @forelse($cajas as $caja)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-medium text-slate-400">
                                    #{{ $caja->id }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 dark:text-white">
                                        {{ $caja->usuario?->name ?? 'Usuario #'.$caja->usuario_id }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        {{ $caja->usuario?->usuario ?? '' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-800 dark:text-white">
                                        {{ $caja->sucursal?->nombre ?? 'Sucursal '.$caja->sucursal_id }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Punto Venta: {{ $caja->puntoVenta?->codigo ?? 0 }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium">
                                        {{ $caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y H:i') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[140px]" title="{{ $caja->descripcion }}">
                                        {{ $caja->descripcion }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium">
                                        {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : '-' }}
                                    </div>
                                    @if($caja->descripcion_cierre)
                                        <div class="text-[11px] text-slate-400 truncate max-w-[140px]" title="{{ $caja->descripcion_cierre }}">
                                            {{ $caja->descripcion_cierre }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-medium">
                                    Bs. {{ number_format((float) $caja->monto_apertura, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-semibold text-amber-600 dark:text-amber-400">
                                    Bs. {{ number_format((float) $caja->venta_contado, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-500">
                                    Bs. {{ number_format((float) ($caja->total_qr + $caja->total_transferencia), 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                                    Bs. {{ number_format((float) ($caja->monto_cierre ?? $caja->saldo), 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(in_array($caja->estado, ['ABIERTA', 'Abierta']))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                            ABIERTA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                                            CERRADA
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(in_array($caja->estado, ['ABIERTA', 'Abierta']))
                                        @permiso('cajas.control.cerrar')
                                            <button wire:click="abrirModalCierre({{ $caja->id }})" 
                                                    class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                                                    title="Arqueo y Cierre">
                                                <i class="fas fa-calculator text-xs"></i>
                                            </button>
                                        @endpermiso
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-cash-register text-3xl text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-sm">No se encontraron turnos de caja registrados.</p>
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
            {{ $cajas->links() }}
        </div>
    </div>

    <!-- MODAL: APERTURA DE CAJA -->
    @if($showAperturaModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showAperturaModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                            <i class="fas fa-key text-sm"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Apertura de Turno de Caja</h3>
                    </div>
                    <button wire:click="$set('showAperturaModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarApertura" class="p-5 space-y-4">
                    <!-- Información de Sucursal y Punto de Venta Asignados Automáticamente -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fas fa-building text-teal-600 dark:text-teal-400"></i> Sucursal:
                            </span>
                            <span class="font-bold text-slate-800 dark:text-white">
                                {{ $aperturaSucursalNombre }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fas fa-desktop text-teal-600 dark:text-teal-400"></i> Punto de Venta:
                            </span>
                            <span class="font-bold text-slate-800 dark:text-white">
                                {{ $aperturaPuntoVentaNombre }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fas fa-user-circle text-teal-600 dark:text-teal-400"></i> Cajero / Operador:
                            </span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ Auth::user()->name }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/20 px-3 py-1.5 rounded-lg border border-teal-200/50 dark:border-teal-800/30">
                        <i class="fas fa-shield-alt text-xs"></i>
                        <span>Punto de Venta asignado automáticamente según el perfil del usuario autenticado.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Fondo Inicial en Efectivo (Bs.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.5" wire:model="montoApertura" placeholder="0.00"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">Monto en billetes/monedas con el que inicia su turno.</span>
                        @error('montoApertura') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Observación / Descripción
                        </label>
                        <input type="text" wire:model="descripcionApertura" placeholder="Ej: Turno Mañana / Caja 1"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showAperturaModal', false)"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none disabled:opacity-50">
                            <span wire:loading.remove wire:target="guardarApertura">Aperturar Turno</span>
                            <span wire:loading wire:target="guardarApertura"><i class="fas fa-spinner fa-spin mr-1"></i> Aperturando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: ARQUEO Y CIERRE DE CAJA -->
    @if($showCierreModal)
        @php
            $cajaCierre = \App\Models\Caja::find($cajaIdACerrar);
            $esperadoEf = $cajaCierre ? ((float) $cajaCierre->monto_apertura + (float) $cajaCierre->venta_contado + (float) $cajaCierre->otro_ingreso - (float) $cajaCierre->total_salida) : 0;
            $difArqueo = (float) $montoCierreEfectivo - $esperadoEf;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showCierreModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <i class="fas fa-calculator text-sm"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Arqueo y Cierre de Caja #{{ $cajaIdACerrar }}</h3>
                    </div>
                    <button wire:click="$set('showCierreModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarCierre" class="p-5 space-y-4">
                    <!-- Resumen del Arqueo -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Fondo Inicial Apertura:</span>
                            <span class="font-mono font-medium text-slate-700 dark:text-slate-200">Bs. {{ number_format((float) ($cajaCierre?->monto_apertura ?? 0), 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Ventas en Efectivo:</span>
                            <span class="font-mono font-medium text-amber-600 dark:text-amber-400">+ Bs. {{ number_format((float) ($cajaCierre?->venta_contado ?? 0), 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Ingresos Menores:</span>
                            <span class="font-mono font-medium text-emerald-600">+ Bs. {{ number_format((float) ($cajaCierre?->otro_ingreso ?? 0), 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Egresos / Salidas:</span>
                            <span class="font-mono font-medium text-rose-600">- Bs. {{ number_format((float) ($cajaCierre?->total_salida ?? 0), 2) }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between font-bold text-sm">
                            <span class="text-slate-800 dark:text-white">Total Esperado en Efectivo:</span>
                            <span class="font-mono text-emerald-600 dark:text-emerald-400">Bs. {{ number_format($esperadoEf, 2) }}</span>
                        </div>
                        <div class="pt-1 text-[11px] text-slate-400 flex justify-between">
                            <span>Ventas QR / Transferencia (En Banco):</span>
                            <span class="font-mono">Bs. {{ number_format((float) (($cajaCierre?->total_qr ?? 0) + ($cajaCierre?->total_transferencia ?? 0)), 2) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Efectivo Físico Contado (Arqueo en Mano) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" wire:model.live="montoCierreEfectivo"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('montoCierreEfectivo') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Indicador de Sobrante / Faltante -->
                    <div class="p-3 rounded-xl text-xs flex items-center justify-between {{ $difArqueo == 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300' : ($difArqueo > 0 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300') }}">
                        <span class="font-semibold">
                            <i class="fas {{ $difArqueo == 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }} mr-1"></i>
                            {{ $difArqueo == 0 ? 'Arqueo Cuadrado Perfecto' : ($difArqueo > 0 ? 'Sobrante detectado' : 'Faltante detectado') }}
                        </span>
                        <span class="font-mono font-bold">
                            {{ $difArqueo >= 0 ? '+ ' : '- ' }}Bs. {{ number_format(abs($difArqueo), 2) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Observación de Cierre
                        </label>
                        <input type="text" wire:model="descripcionCierre" placeholder="Ej: Arqueo conforme / Entrega al administrador"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showCierreModal', false)"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none disabled:opacity-50">
                            <span wire:loading.remove wire:target="guardarCierre">Confirmar Cierre de Caja</span>
                            <span wire:loading wire:target="guardarCierre"><i class="fas fa-spinner fa-spin mr-1"></i> Cerrando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: MOVIMIENTO MENOR DE CAJA (INGRESO / EGRESO) -->
    @if($showMovimientoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.set('showMovimientoModal', false)">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                            <i class="fas fa-exchange-alt text-sm"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Movimiento Extraordinario de Caja</h3>
                    </div>
                    <button wire:click="$set('showMovimientoModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarMovimiento" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tipo de Operación <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="tipoMovimientoCaja" 
                                class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="INGRESO">➕ INGRESO (Entrada extraordinaria de dinero)</option>
                            <option value="SALIDA">➖ EGRESO / SALIDA (Gasto menor / Retiro por seguridad)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Monto en Efectivo (Bs.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.5" wire:model="montoMovimientoCaja" placeholder="0.00"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('montoMovimientoCaja') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Motivo / Justificación <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="motivoMovimientoCaja" placeholder="Ej: Compra de insumos de limpieza / Sencillo adicional"
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('motivoMovimientoCaja') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showMovimientoModal', false)"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-all focus:outline-none disabled:opacity-50">
                            <span wire:loading.remove wire:target="guardarMovimiento">Registrar Movimiento</span>
                            <span wire:loading wire:target="guardarMovimiento"><i class="fas fa-spinner fa-spin mr-1"></i> Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
