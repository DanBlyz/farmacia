<div>
    <x-slot name="header">
        Eventos Significativos SIAT
    </x-slot>

    <!-- Notificación Flash de Éxito -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 6000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shrink-0 shadow-md shadow-emerald-500/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                </div>
                <span class="font-medium text-sm">{{ session('message') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    <!-- Notificación Flash de Error -->
    @if (session()->has('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 7000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 text-rose-800 dark:text-rose-300 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-500 flex items-center justify-center text-white shrink-0 shadow-md shadow-rose-500/30">
                    <i class="fas fa-exclamation-triangle text-xs"></i>
                </div>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    <!-- KPIs Estadísticos Rápidos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- KPI 1: Total Eventos -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <i class="fas fa-satellite-dish text-base"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Eventos</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($kpis['total']) }}</h3>
            </div>
        </div>

        <!-- KPI 2: Eventos Hoy -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-check text-base"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Registrados Hoy</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($kpis['hoy']) }}</h3>
            </div>
        </div>

        <!-- KPI 3: Punto de Venta 1 -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i class="fas fa-cash-register text-base"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Punto de Venta 1</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($kpis['pv1']) }}</h3>
            </div>
        </div>

        <!-- KPI 4: Central (PV 0) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i class="fas fa-building text-base"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Central (PV 0)</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($kpis['pv0']) }}</h3>
            </div>
        </div>
    </div>

    <!-- Tarjeta Principal Enmarcada -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
        
        <!-- 1. Cabecera y Botón Nuevo Evento -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                    <i class="fas fa-exclamation-circle text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">
                        Registro de Eventos Significativos (Fase V SIAT)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Homologación y declaración oficial de contingencias ante el Servicio de Impuestos Nacionales
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                @permiso('sincronizacion.paquetes.validar')
                <button type="button" 
                        wire:click="validarTodosLosPaquetes"
                        wire:loading.attr="disabled"
                        wire:target="validarTodosLosPaquetes"
                        title="Valida en lote todos los paquetes registrados pendientes de verificación en el SIAT"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm border border-slate-300 dark:border-slate-700 shadow-2xs transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="validarTodosLosPaquetes" class="flex items-center gap-2">
                        <i class="fas fa-sync text-teal-600 dark:text-teal-400 text-xs"></i>
                        <span>Validar Paquetes SIAT ({{ \App\Models\PaqueteFactura::whereIn('codigo_estado', [901, null])->orWhere('estado', 'PENDIENTE')->count() }})</span>
                    </span>
                    <span wire:loading wire:target="validarTodosLosPaquetes" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin text-teal-600 dark:text-teal-400 text-xs"></i>
                        <span>Validando en SIAT...</span>
                    </span>
                </button>
                @endpermiso

                @permiso('sincronizacion.eventos.crear')
                <button type="button" 
                        wire:click="abrirModalCrear"
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Nuevo Evento Significativo</span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra Superior de Control y Filtros (Regla 9) -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pt-2">
            <!-- Izquierda: Selector de Registros + Filtros Secundarios -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Selector Paginación -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Mostrar</span>
                    <select wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">registros</span>
                </div>

                <!-- Filtro Punto de Venta -->
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="filtroPuntoVentaId" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="">Todos los Puntos de Venta</option>
                        @foreach($puntosVenta as $pv)
                            <option value="{{ $pv->id }}">PV {{ $pv->codigo }} - {{ $pv->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Motivo -->
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="filtroMotivoId" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer max-w-xs truncate">
                        <option value="">Todos los Motivos</option>
                        @foreach($tiposEvento as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->codigo_clasificador }}. {{ Str::limit($tipo->descripcion, 35) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Estado -->
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="">Todos los Estados</option>
                        <option value="REGISTRADO">REGISTRADO</option>
                        <option value="CERRADO">CERRADO</option>
                        <option value="EN_CURSO">EN_CURSO</option>
                    </select>
                </div>
            </div>

            <!-- Derecha: Buscador en Tiempo Real -->
            <div class="relative w-full lg:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por código, descripción..." 
                       class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs outline-none transition-all">
                @if($search !== '')
                    <button type="button" 
                            wire:click="$set('search', '')" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times-circle text-xs"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- 3. Tabla Enmarcada Clásica -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3.5 text-center">Cód. Recepción</th>
                        <th class="py-3 px-3.5">Punto de Venta</th>
                        <th class="py-3 px-3.5">Motivo del Evento</th>
                        <th class="py-3 px-3.5">Periodo del Evento</th>
                        <th class="py-3 px-3.5">Descripción</th>
                        <th class="py-3 px-3.5 text-center">Estado</th>
                        <th class="py-3 px-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($eventos as $ev)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Código Recepción SIAT -->
                            <td class="py-3 px-3.5 text-center">
                                @if($ev->codigo_recepcion)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                        <i class="fas fa-check-double text-[10px]"></i>
                                        {{ $ev->codigo_recepcion }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Sin código</span>
                                @endif
                            </td>

                            <!-- Punto de Venta -->
                            <td class="py-3 px-3.5">
                                <div class="font-bold text-slate-800 dark:text-white">
                                    PV {{ $ev->puntoVenta?->codigo ?? 0 }} - {{ $ev->puntoVenta?->nombre ?? 'Central' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $ev->puntoVenta?->sucursal?->nombre ?? 'Casa Matriz' }}
                                </div>
                            </td>

                            <!-- Motivo del Evento -->
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        Cód {{ $ev->tipoEvento?->codigo_clasificador ?? $ev->tipo_evento_significativo_id }}
                                    </span>
                                </div>
                                <div class="font-medium text-slate-700 dark:text-slate-200 mt-0.5 max-w-xs truncate" title="{{ $ev->tipoEvento?->descripcion }}">
                                    {{ $ev->tipoEvento?->descripcion ?? 'Motivo '.$ev->tipo_evento_significativo_id }}
                                </div>
                            </td>

                            <!-- Periodo de Afectación -->
                            <td class="py-3 px-3.5">
                                <div class="text-slate-700 dark:text-slate-200 font-mono text-[11px]">
                                    <span class="font-semibold text-slate-500">De:</span> {{ \Carbon\Carbon::parse($ev->fecha_ini)->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-slate-700 dark:text-slate-200 font-mono text-[11px]">
                                    <span class="font-semibold text-slate-500">A:</span> &nbsp;{{ \Carbon\Carbon::parse($ev->fecha_fin)->format('d/m/Y H:i') }}
                                </div>
                                @php
                                    $diff = \Carbon\Carbon::parse($ev->fecha_ini)->diffForHumans(\Carbon\Carbon::parse($ev->fecha_fin), true);
                                @endphp
                                <span class="text-[10px] text-slate-400">Duración: {{ $diff }}</span>
                            </td>

                            <!-- Descripción / Justificación -->
                            <td class="py-3 px-3.5">
                                <div class="max-w-xs text-slate-600 dark:text-slate-300 truncate" title="{{ $ev->descripcion }}">
                                    {{ $ev->descripcion }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Por: {{ $ev->usuarioCreador?->name ?? 'Sistema' }}
                                </div>
                            </td>

                            <!-- Estado -->
                            <td class="py-3 px-3.5 text-center">
                                @if($ev->estado === 'REGISTRADO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        REGISTRADO
                                    </span>
                                @elseif($ev->estado === 'CERRADO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        CERRADO
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        {{ $ev->estado }}
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            wire:click="verDetalle({{ $ev->id }})"
                                            title="Ver Detalle"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs transition duration-150 cursor-pointer inline-flex items-center gap-1.5">
                                        <i class="fas fa-eye text-teal-500 text-xs"></i>
                                        <span>Ver</span>
                                    </button>

                                    @permiso('sincronizacion.paquetes.enviar')
                                    <button type="button" 
                                            wire:click="abrirModalPaquete({{ $ev->id }})"
                                            title="Gestionar y Regularizar Paquetes de Facturas (Fase VI)"
                                            class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/20 font-semibold text-xs transition duration-150 cursor-pointer inline-flex items-center gap-1.5">
                                        <i class="fas fa-boxes-packing text-amber-600 dark:text-amber-400 text-xs"></i>
                                        <span>Paquetes</span>
                                        @if($ev->paquetes->count() > 0)
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white leading-none">
                                                {{ $ev->paquetes->count() }}
                                            </span>
                                        @endif
                                    </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                        <i class="fas fa-calendar-times text-lg"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        @if($search !== '' || $filtroPuntoVentaId || $filtroMotivoId || $filtroEstado)
                                            No se encontraron eventos significativos con los filtros seleccionados
                                        @else
                                            No hay eventos significativos registrados en el sistema
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        @if($search !== '' || $filtroPuntoVentaId || $filtroMotivoId || $filtroEstado)
                                            Intente restablecer los filtros para ver todos los registros.
                                        @else
                                            Haga clic en "+ Nuevo Evento Significativo" para transmitir una contingencia al SIAT.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 4. Paginación -->
        @if ($eventos->hasPages())
            <div class="pt-2">
                {{ $eventos->links() }}
            </div>
        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL DE NUEVO EVENTO SIGNIFICATIVO -->
    <!-- ========================================================================= -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay oscuro -->
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 transition-opacity" 
             wire:click="cerrarModalCrear"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-800">
                
                <!-- Cabecera del Modal -->
                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                            <i class="fas fa-satellite-dish text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white" id="modal-title">
                                Registrar y Transmitir Evento Significativo
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Transmisión formal SOAP ante WebService SIAT Facturación Operaciones
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            wire:click="cerrarModalCrear" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="guardarEvento">
                    <div class="p-6 space-y-4">

                        <!-- Aviso Normativo SIAT -->
                        <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-300 text-xs flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 text-base mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="font-bold">Normativa Técnica SIAT (RND 102100000011):</strong>
                                <p class="mt-0.5 text-blue-800 dark:text-blue-300/90 leading-relaxed">
                                    Los eventos significativos regularizan las emisiones fuera de línea. Los intervalos de fecha no deben solaparse con eventos ya existentes en el mismo Punto de Venta y deben pertenecer al periodo de vigencia del CUFD del evento.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Punto de Venta -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Punto de Venta <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="punto_venta_id" 
                                        class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2.5 px-3 outline-none">
                                    @foreach($puntosVenta as $pv)
                                        <option value="{{ $pv->id }}">PV {{ $pv->codigo }} - {{ $pv->nombre }} ({{ $pv->sucursal?->nombre }})</option>
                                    @endforeach
                                </select>
                                @error('punto_venta_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Tipo de Evento (Motivo Clasificador SIN) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Motivo del Evento (SIN) <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model.live="tipo_evento_significativo_id" 
                                        class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2.5 px-3 outline-none">
                                    @foreach($tiposEvento as $tipo)
                                        <option value="{{ $tipo->id }}">Cód {{ $tipo->codigo_clasificador }} - {{ $tipo->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_evento_significativo_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Fecha y Hora Inicio -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha y Hora Inicio <span class="text-rose-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       wire:model="fecha_ini" 
                                       class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2.5 px-3 outline-none">
                                @error('fecha_ini') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Fecha y Hora Fin -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha y Hora Fin <span class="text-rose-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       wire:model="fecha_fin" 
                                       class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2.5 px-3 outline-none">
                                @error('fecha_fin') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Justificación / Descripción -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Justificación / Descripción del Evento <span class="text-rose-500">*</span>
                            </label>
                            <textarea wire:model="descripcion" 
                                      rows="3" 
                                      placeholder="Detalle los motivos y alcance de la contingencia técnica..." 
                                      class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none"></textarea>
                            @error('descripcion') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <!-- Botones de Acción -->
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" 
                                wire:click="cerrarModalCrear" 
                                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="guardarEvento" class="flex items-center gap-2">
                                <i class="fas fa-paper-plane text-xs"></i>
                                <span>Transmitir a Impuestos</span>
                            </span>
                            <span wire:loading wire:target="guardarEvento" class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin text-xs"></i>
                                <span>Transmitiendo SOAP...</span>
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL DE DETALLE DEL EVENTO SIGNIFICATIVO -->
    <!-- ========================================================================= -->
    @if($showDetalleModal && $eventoDetalle)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay oscuro -->
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 transition-opacity" 
             wire:click="cerrarModalDetalle"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-800">
                
                <!-- Cabecera -->
                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                            <i class="fas fa-file-invoice text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white">
                                Detalle de Evento Significativo #{{ $eventoDetalle->id }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Código de Recepción SIAT: {{ $eventoDetalle->codigo_recepcion ?? 'Pendiente' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            wire:click="cerrarModalDetalle" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block font-medium">Punto de Venta</span>
                            <span class="font-bold text-slate-800 dark:text-white text-sm">
                                PV {{ $eventoDetalle->puntoVenta?->codigo }} - {{ $eventoDetalle->puntoVenta?->nombre }}
                            </span>
                            <span class="text-slate-500 block text-[11px]">
                                {{ $eventoDetalle->puntoVenta?->sucursal?->nombre }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block font-medium">Estado del Evento</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 mt-1">
                                {{ $eventoDetalle->estado }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-400 block font-medium">Motivo Clasificado por SIN</span>
                        <span class="font-bold text-slate-800 dark:text-white text-sm block mt-0.5">
                            Código {{ $eventoDetalle->tipoEvento?->codigo_clasificador }}: {{ $eventoDetalle->tipoEvento?->descripcion }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block font-medium">Fecha/Hora Inicio</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-white">
                                {{ \Carbon\Carbon::parse($eventoDetalle->fecha_ini)->format('d/m/Y H:i:s') }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block font-medium">Fecha/Hora Fin</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-white">
                                {{ \Carbon\Carbon::parse($eventoDetalle->fecha_fin)->format('d/m/Y H:i:s') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-400 block font-medium">Descripción / Justificación</span>
                        <p class="text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                            {{ $eventoDetalle->descripcion }}
                        </p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-400 block font-medium">CUFD del Evento</span>
                        <p class="text-slate-700 dark:text-slate-300 font-mono text-[11px] break-all mt-1">
                            {{ $eventoDetalle->cufdEvento?->codigo ?? 'Mismo CUFD Activo' }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-200 dark:border-slate-800 text-[11px]">
                        <span>Registrado por: <strong>{{ $eventoDetalle->usuarioCreador?->name ?? 'Sistema' }}</strong></span>
                        <span>Fecha de Registro: {{ $eventoDetalle->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end">
                    <button type="button" 
                            wire:click="cerrarModalDetalle" 
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs cursor-pointer">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL DE EMPAQUETADO Y REGULARIZACIÓN DE PAQUETES (FASE VI SIAT) -->
    <!-- ========================================================================= -->
    @if($showPaqueteModal && $eventoPaquete)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-paquete" role="dialog" aria-modal="true">
        <!-- Overlay oscuro -->
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 transition-opacity" 
             wire:click="cerrarModalPaquete"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-200 dark:border-slate-800">
                
                <!-- Cabecera del Modal -->
                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white shadow-sm shrink-0">
                            <i class="fas fa-boxes-packing text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white" id="modal-title-paquete">
                                Emisión y Regularización de Paquetes (Etapa VI SIAT)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Transmisión TAR.GZ de facturas en contingencia y validación de estado fiscal ante el SIN
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            wire:click="cerrarModalPaquete" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="p-6 space-y-6 text-xs max-h-[75vh] overflow-y-auto">

                    <!-- Tarjeta de Contexto del Evento -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <span class="text-slate-400 block font-medium text-[11px]">Punto de Venta</span>
                            <span class="font-bold text-slate-800 dark:text-white text-xs">
                                PV {{ $eventoPaquete->puntoVenta?->codigo }} - {{ $eventoPaquete->puntoVenta?->nombre }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium text-[11px]">Motivo del Evento</span>
                            <span class="font-bold text-slate-800 dark:text-white text-xs truncate block" title="{{ $eventoPaquete->tipoEvento?->descripcion }}">
                                Cód {{ $eventoPaquete->tipoEvento?->codigo_clasificador }}: {{ $eventoPaquete->tipoEvento?->descripcion }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium text-[11px]">Intervalo Contingencia</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300 text-[11px] block">
                                {{ \Carbon\Carbon::parse($eventoPaquete->fecha_ini)->format('d/m H:i') }} &rarr; {{ \Carbon\Carbon::parse($eventoPaquete->fecha_fin)->format('d/m H:i') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium text-[11px]">Cód. Recepción Evento</span>
                            <span class="font-mono font-bold text-teal-600 dark:text-teal-400 text-xs block">
                                {{ $eventoPaquete->codigo_recepcion ?? 'Sin código' }}
                            </span>
                        </div>
                    </div>

                    <!-- Sección 1: Facturas en Contingencia Pendientes de Empaquetar -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-800 dark:text-white text-sm">
                                    Facturas en Contingencia para Empaquetar
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $facturasContingencia->count() > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $facturasContingencia->count() }} pendientes
                                </span>
                            </div>

                            @if($facturasContingencia->count() > 0)
                                @permiso('sincronizacion.paquetes.enviar')
                                <button type="button" 
                                        wire:click="enviarPaquete"
                                        wire:loading.attr="disabled"
                                        class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                    <span wire:loading.remove wire:target="enviarPaquete" class="flex items-center gap-2">
                                        <i class="fas fa-file-zipper text-xs"></i>
                                        <span>Generar Paquete TAR.GZ y Enviar a SIAT</span>
                                    </span>
                                    <span wire:loading wire:target="enviarPaquete" class="flex items-center gap-2">
                                        <i class="fas fa-spinner fa-spin text-xs"></i>
                                        <span>Empaquetando y Transmitiendo...</span>
                                    </span>
                                </button>
                                @endpermiso
                            @endif
                        </div>

                        @if($facturasContingencia->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 max-h-56 overflow-y-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="py-2.5 px-3">N° Factura</th>
                                            <th class="py-2.5 px-3">Fecha y Hora</th>
                                            <th class="py-2.5 px-3">Cliente / NIT</th>
                                            <th class="py-2.5 px-3 text-right">Total (Bs)</th>
                                            <th class="py-2.5 px-3 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach($facturasContingencia as $fac)
                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                                <td class="py-2 px-3 font-mono font-bold text-slate-800 dark:text-white">
                                                    #{{ $fac->numero_factura }}
                                                </td>
                                                <td class="py-2 px-3 text-slate-600 dark:text-slate-300 font-mono text-[11px]">
                                                    {{ \Carbon\Carbon::parse($fac->fecha)->format('d/m/Y H:i:s') }}
                                                </td>
                                                <td class="py-2 px-3">
                                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block truncate max-w-xs">{{ $fac->razon_social ?: ($fac->cliente?->razon_social ?? 'S/N') }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">NIT/CI: {{ $fac->nit }}</span>
                                                </td>
                                                <td class="py-2 px-3 text-right font-bold text-slate-800 dark:text-white font-mono">
                                                    Bs. {{ number_format($fac->total, 2) }}
                                                </td>
                                                <td class="py-2 px-3 text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                        {{ $fac->estado }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-800 text-center text-slate-500 dark:text-slate-400">
                                <i class="fas fa-check-circle text-emerald-500 text-lg mb-1 block"></i>
                                <span class="font-medium">No existen facturas en contingencia pendientes de envío para este evento.</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Todas las facturas correspondientes ya fueron regularizadas o no se emitieron ventas fuera de línea.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Sección 2: Paquetes Transmitidos -->
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 dark:text-white text-sm">
                                Historial de Paquetes Transmitidos
                            </h4>
                            <span class="text-xs text-slate-400">
                                {{ $eventoPaquete->paquetes->count() }} paquetes registrados
                            </span>
                        </div>

                        @if($eventoPaquete->paquetes->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="py-2.5 px-3">Fecha y Hora</th>
                                            <th class="py-2.5 px-3">Cód. Recepción Paquete</th>
                                            <th class="py-2.5 px-3 text-center">Cant. Facturas</th>
                                            <th class="py-2.5 px-3 text-center">Estado SIAT</th>
                                            <th class="py-2.5 px-3 text-right">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach($eventoPaquete->paquetes as $paq)
                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300 font-mono text-[11px]">
                                                    {{ $paq->fecha_envio ? \Carbon\Carbon::parse($paq->fecha_envio)->format('d/m/Y H:i:s') : $paq->created_at->format('d/m/Y H:i:s') }}
                                                    <span class="text-[10px] text-slate-400 block">Por: {{ $paq->usuarioCreador?->name ?? 'Sistema' }}</span>
                                                </td>
                                                <td class="py-2.5 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                                    {{ $paq->codigo_recepcion }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-bold text-slate-800 dark:text-white">
                                                    {{ $paq->cantidad_facturas }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    @if($paq->codigo_estado == 908 || $paq->estado === 'VALIDADA')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                                            908 VALIDADA
                                                        </span>
                                                    @elseif($paq->codigo_estado == 901 || $paq->estado === 'PENDIENTE')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                            901 PENDIENTE
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                            {{ $paq->codigo_estado }} {{ $paq->estado }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    @permiso('sincronizacion.paquetes.validar')
                                                    <button type="button" 
                                                            wire:click="validarPaquete({{ $paq->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="validarPaquete({{ $paq->id }})"
                                                            class="px-2.5 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/40 dark:hover:bg-teal-900/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 font-semibold text-xs transition duration-150 cursor-pointer inline-flex items-center gap-1.5 disabled:opacity-50">
                                                        <span wire:loading.remove wire:target="validarPaquete({{ $paq->id }})" class="flex items-center gap-1.5">
                                                            <i class="fas fa-rotate text-teal-600 text-[10px]"></i>
                                                            <span>Validar en SIAT</span>
                                                        </span>
                                                        <span wire:loading wire:target="validarPaquete({{ $paq->id }})" class="flex items-center gap-1.5">
                                                            <i class="fas fa-spinner fa-spin text-teal-600 text-[10px]"></i>
                                                            <span>Consultando...</span>
                                                        </span>
                                                    </button>
                                                    @endpermiso
                                                </td>
                                            </tr>
                                            <!-- Facturas asociadas a este paquete -->
                                            @if($paq->facturas && $paq->facturas->count() > 0)
                                                <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                                    <td colspan="5" class="py-2.5 px-4 text-xs">
                                                        <div class="flex items-center gap-2 mb-1.5 text-slate-500 dark:text-slate-400 font-medium text-[11px]">
                                                            <i class="fas fa-file-invoice text-teal-600 dark:text-teal-400"></i>
                                                            <span>Facturas incluidas en este paquete ({{ $paq->facturas->count() }}):</span>
                                                        </div>
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            @foreach($paq->facturas as $facPaq)
                                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono border {{ $facPaq->estado === 'VALIDADA' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800' }}">
                                                                    <strong>#{{ $facPaq->numero_factura }}</strong>
                                                                    <span class="text-slate-500 dark:text-slate-400 font-sans text-[11px]">Bs. {{ number_format($facPaq->total, 2) }}</span>
                                                                    <span class="text-[10px] font-sans font-bold px-1.5 py-0.2 rounded-full {{ $facPaq->estado === 'VALIDADA' ? 'bg-emerald-200 dark:bg-emerald-800 text-emerald-900 dark:text-emerald-100' : 'bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100' }}">
                                                                        {{ $facPaq->estado }}
                                                                    </span>
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                            @if(!empty($paq->mensajes_validacion))
                                                <tr class="bg-rose-50/50 dark:bg-rose-950/20">
                                                    <td colspan="5" class="py-2 px-3 text-rose-700 dark:text-rose-300 text-[11px]">
                                                        <strong>Observaciones SIN:</strong> 
                                                        {{ is_array($paq->mensajes_validacion) ? json_encode($paq->mensajes_validacion, JSON_UNESCAPED_UNICODE) : $paq->mensajes_validacion }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-800 text-center text-slate-400">
                                Ningún paquete generado para este evento hasta el momento.
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end">
                    <button type="button" 
                            wire:click="cerrarModalPaquete" 
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs cursor-pointer">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
