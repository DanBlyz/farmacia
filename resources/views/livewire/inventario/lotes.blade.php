<div class="space-y-6">
    <x-slot name="header">
        Lotes y Vencimientos
    </x-slot>
    <!-- 1. Tarjetas Métricas KPI de Semáforo Sanitario -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Lotes -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Lotes</p>
                    <p class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">{{ number_format($kpi['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>

        <!-- Óptimos -->
        <div class="bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-950/60 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Óptimos (> 60d)</p>
                    <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($kpi['optimos']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Próximos a Vencer -->
        <div class="bg-white dark:bg-slate-900 border border-amber-100 dark:border-amber-950/60 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400 uppercase tracking-wider">Próx. Vencer (< 60d)</p>
                    <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300 mt-1">{{ number_format($kpi['proximos']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- Caducados -->
        <div class="bg-white dark:bg-slate-900 border border-rose-100 dark:border-rose-950/60 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-rose-600 dark:text-rose-400 uppercase tracking-wider">Vencidos (Bloqueados)</p>
                    <p class="text-2xl font-extrabold text-rose-700 dark:text-rose-300 mt-1">{{ number_format($kpi['vencidos']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    <i class="fas fa-ban"></i>
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
                    <i class="fas fa-boxes text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">
                        Control de Lotes & Vencimientos (FEFO)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Monitoreo de caducidades, existencia física por sucursal y dispensación prioritaria por vencimiento más próximo
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('inventario.lotes.crear')
                <button type="button" 
                        wire:click="create" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="create" class="flex items-center gap-2">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Nuevo Lote Físico</span>
                    </span>
                    <span wire:loading wire:target="create" class="flex items-center gap-2">
                        <i class="fas fa-circle-notch fa-spin text-xs"></i>
                        <span>Cargando...</span>
                    </span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra Superior de Control y Filtros (Regla 9) -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pt-1">
            <!-- Izquierda: Paginación Dinámica -->
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                <select id="perPage" 
                        wire:model.live="perPage" 
                        class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
            </div>

            <!-- Centro: Filtros Específicos -->
            <div class="flex flex-col sm:flex-row items-center gap-2.5 flex-1 max-w-xl">
                <!-- Filtro Sucursal -->
                <div class="w-full sm:w-1/2">
                    <select wire:model.live="filtroSucursalId" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="">Todas las Sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Semáforo -->
                <div class="w-full sm:w-1/2">
                    <select wire:model.live="filtroSemaforo" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-2.5 shadow-2xs outline-none cursor-pointer">
                        <option value="todos">Todos los Estados FEFO</option>
                        <option value="optimo">🟢 Solo Óptimos (> 60 días)</option>
                        <option value="proximo">🟠 Próximos a Vencer (< 60 días)</option>
                        <option value="vencido">🔴 Ya Caducados</option>
                    </select>
                </div>
            </div>

            <!-- Derecha: Buscador Debounce con Botón de Limpieza -->
            <div class="relative w-full lg:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar lote, medicamento o lab..." 
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
                        <th class="py-3 px-4 text-center">Nº Lote</th>
                        <th class="py-3 px-4">Medicamento / Principio Activo</th>
                        <th class="py-3 px-4">Sucursal</th>
                        <th class="py-3 px-4 text-center">Fecha Caducidad</th>
                        <th class="py-3 px-4 text-center">Semáforo FEFO</th>
                        <th class="py-3 px-4 text-center">Stock Actual</th>
                        <th class="py-3 px-4 text-right">Precios (C / V)</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse($lotes as $lote)
                        @php
                            $esVencido = $lote->esVencido();
                            $esProximo = $lote->esProximoAVencer(60);
                            $diasRestantes = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($lote->fecha_vencimiento)->startOfDay(), false);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $esVencido ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
                            <!-- Nº Lote & Ubicación -->
                            <td class="py-3 px-4 text-center">
                                <span class="font-mono text-xs font-bold text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2.5 py-1 rounded-lg border border-teal-200 dark:border-teal-800 block">
                                    {{ $lote->numero_lote }}
                                </span>
                                @if($lote->ubicacion_estante)
                                    <span class="text-[10px] text-slate-400 block mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $lote->ubicacion_estante }}
                                    </span>
                                @endif
                            </td>

                            <!-- Medicamento -->
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800 dark:text-white">
                                    {{ $lote->producto->nombre ?? 'N/A' }}
                                </div>
                                @if($lote->producto?->principio_activo)
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $lote->producto->principio_activo }}
                                    </div>
                                @endif
                                <div class="text-[10px] text-slate-400">
                                    {{ $lote->producto?->proveedor?->razon_social ?: ($lote->producto?->proveedor?->nombre ?: 'Sin proveedor') }}
                                </div>
                            </td>

                            <!-- Sucursal -->
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300">
                                <span class="font-medium text-slate-700 dark:text-slate-200 block">
                                    {{ $lote->sucursal->nombre ?? 'N/A' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    {{ $lote->sucursal->codigo_sucursal ?? '' }}
                                </span>
                            </td>

                            <!-- Fecha Caducidad -->
                            <td class="py-3 px-4 text-center font-mono">
                                <span class="font-semibold {{ $esVencido ? 'text-rose-600 dark:text-rose-400 font-bold' : ($esProximo ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-700 dark:text-slate-300') }}">
                                    {{ $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}
                                </span>
                                <span class="block text-[10px] {{ $esVencido ? 'text-rose-500 font-bold' : ($esProximo ? 'text-amber-500 font-semibold' : 'text-slate-400') }}">
                                    @if($esVencido)
                                        Venció hace {{ abs($diasRestantes) }} d
                                    @elseif($diasRestantes === 0)
                                        Vence hoy
                                    @else
                                        Faltan {{ $diasRestantes }} d
                                    @endif
                                </span>
                            </td>

                            <!-- Semáforo FEFO -->
                            <td class="py-3 px-4 text-center">
                                @if($esVencido)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <i class="fas fa-ban text-[10px]"></i> Caducado
                                    </span>
                                @elseif($esProximo)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> Próx. Vencer
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i class="fas fa-check-circle text-[10px]"></i> Óptimo
                                    </span>
                                @endif
                            </td>

                            <!-- Stock Actual -->
                            <td class="py-3 px-4 text-center">
                                <span class="font-bold text-sm {{ $lote->stock_actual <= $lote->stock_minimo_alerta ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-white' }}">
                                    {{ number_format($lote->stock_actual, 0) }}
                                </span>
                                <span class="block text-[10px] text-slate-400">
                                    Mín: {{ number_format($lote->stock_minimo_alerta, 0) }}
                                </span>
                            </td>

                            <!-- Precios Compra / Venta -->
                            <td class="py-3 px-4 text-right">
                                <span class="font-bold text-slate-800 dark:text-slate-100">
                                    Bs. {{ number_format($lote->precio_venta, 2) }}
                                </span>
                                <span class="block text-[10px] text-slate-400">
                                    C: Bs. {{ number_format($lote->precio_compra, 2) }}
                                </span>
                            </td>

                            <!-- Estado del Lote -->
                            <td class="py-3 px-4 text-center">
                                @if($lote->estado === 'ACTIVO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Activo
                                    </span>
                                @elseif($lote->estado === 'BLOQUEADO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                        Bloqueado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        Agotado
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Ajuste de Stock -->
                                    @permiso('inventario.lotes.ajustar')
                                    <button type="button" 
                                            wire:click="abrirAjuste({{ $lote->id }})" 
                                            title="Ajustar Stock / Dar de Baja en Kardex"
                                            class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-900/50 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-sliders-h text-xs"></i>
                                    </button>
                                    @endpermiso

                                    <!-- Bloquear / Desbloquear -->
                                    @permiso('inventario.lotes.bloquear')
                                    <button type="button" 
                                            wire:click="toggleBloqueo({{ $lote->id }})" 
                                            title="{{ $lote->estado === 'BLOQUEADO' ? 'Reactivar Lote' : 'Bloquear Lote (Cuarentena / Alerta)' }}"
                                            class="w-8 h-8 rounded-lg {{ $lote->estado === 'BLOQUEADO' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }} flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas {{ $lote->estado === 'BLOQUEADO' ? 'fa-lock' : 'fa-lock-open' }} text-xs"></i>
                                    </button>
                                    @endpermiso

                                    <!-- Editar -->
                                    @permiso('inventario.lotes.editar')
                                    <button type="button" 
                                            wire:click="edit({{ $lote->id }})" 
                                            title="Modificar Datos del Lote"
                                            class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    @endpermiso

                                    <!-- Eliminar -->
                                    @permiso('inventario.lotes.eliminar')
                                    <button type="button" 
                                            @click="$dispatch('swal:confirm', { 
                                                title: '¿Eliminar Lote?', 
                                                text: 'Esta acción dará de baja el lote {{ $lote->numero_lote }} del registro físico.', 
                                                id: {{ $lote->id }}, 
                                                action: 'delete' 
                                            })" 
                                            title="Eliminar Lote"
                                            class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-boxes text-3xl mb-3 block text-slate-300 dark:text-slate-600"></i>
                                <span class="text-sm font-medium">No se encontraron lotes con los filtros seleccionados.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 4. Paginación Desacoplada -->
        <div class="pt-2">
            {{ $lotes->links() }}
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL DE CREACIÓN / EDICIÓN DE LOTE -->
    <!-- ========================================================================= -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-data @keydown.escape.window="$wire.closeModal()">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-cubes text-teal-600"></i>
                    {{ $loteId ? 'Editar Lote Físico' : 'Nuevo Lote en Almacén' }}
                </h3>
                <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Formulario Modal -->
            <form wire:submit="save" class="p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Medicamento con Buscador Interactivo -->
                    <div class="sm:col-span-2">
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
                        @error('producto_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sucursal -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Sucursal de Destino <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="sucursal_id" 
                                class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Nº de Lote Físico <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" 
                                    wire:click="generarCodigoLote" 
                                    class="text-[10px] text-teal-600 dark:text-teal-400 hover:underline flex items-center gap-1 cursor-pointer font-medium"
                                    title="Regenerar código automático de lote">
                                <i class="fas fa-magic text-[9px]"></i> Autogenerar
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" 
                                   wire:model="numero_lote" 
                                   placeholder="Ej. LOT-202610-00001" 
                                   class="w-full py-2 pl-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-mono font-bold">
                            <button type="button" 
                                    wire:click="generarCodigoLote" 
                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors cursor-pointer"
                                    title="Regenerar código">
                                <i class="fas fa-sync-alt text-xs"></i>
                            </button>
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-1">
                            Código autogenerado para prevenir errores humanos. Puede modificarlo libremente.
                        </span>
                        @error('numero_lote') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha de Vencimiento -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Fecha de Vencimiento <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               wire:model="fecha_vencimiento" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('fecha_vencimiento') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stock Inicial (Solo en creación) -->
                    @if(!$loteId)
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Stock Físico Inicial <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               step="1" 
                               wire:model="stock_actual" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('stock_actual') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @else
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Stock Actual Físico (Lectura)
                        </label>
                        <input type="number" 
                               disabled 
                               value="{{ $stock_actual }}" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-[10px] text-slate-400">Para modificar use el botón "Ajustar Stock".</span>
                    </div>
                    @endif

                    <!-- Stock Mínimo Alerta -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Stock Mínimo de Alerta
                        </label>
                        <input type="number" 
                               step="1" 
                               wire:model="stock_minimo_alerta" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        @error('stock_minimo_alerta') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Ubicación / Estante -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Ubicación Física / Estante
                        </label>
                        <input type="text" 
                               wire:model="ubicacion_estante" 
                               placeholder="Ej. Estante A-2, Vitrina 1" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>

                    <!-- Precio Compra -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Costo de Compra (Bs.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               step="0.01" 
                               wire:model="precio_compra" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-mono">
                        @error('precio_compra') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Precio Venta -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Precio de Venta (Bs.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               step="0.01" 
                               wire:model="precio_venta" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-mono">
                        @error('precio_venta') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado del Lote -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Estado Operativo del Lote
                        </label>
                        <select wire:model="estado" 
                                class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="ACTIVO">ACTIVO (Habilitado para dispensación)</option>
                            <option value="BLOQUEADO">BLOQUEADO (En cuarentena / Alerta sanitaria)</option>
                            <option value="AGOTADO">AGOTADO (Sin existencias físicas)</option>
                        </select>
                    </div>
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
                        <span wire:loading.remove wire:target="save">Guardar Lote</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                            <i class="fas fa-circle-notch fa-spin text-xs"></i>
                            <span>Guardando...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL DE AJUSTE DE STOCK & REGULARIZACIÓN KARDEX -->
    <!-- ========================================================================= -->
    @if($showAjusteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-data @keydown.escape.window="$wire.closeAjusteModal()">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col">
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-sliders-h text-teal-600"></i>
                        Ajuste de Existencias / Baja de Lote
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Lote: <span class="font-mono font-bold text-teal-600">{{ $selectedLote?->numero_lote }}</span> | {{ $selectedLote?->producto?->nombre }}
                    </p>
                </div>
                <button type="button" wire:click="closeAjusteModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Formulario Ajuste -->
            <form wire:submit="guardarAjuste" class="p-6 space-y-4">
                <!-- Info Stock Actual -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
                    <span class="text-slate-600 dark:text-slate-400">Stock Actual en Lote:</span>
                    <span class="font-bold text-sm text-slate-800 dark:text-white font-mono">{{ number_format($selectedLote?->stock_actual, 0) }} Unidades</span>
                </div>

                <!-- Tipo de Ajuste -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tipo de Operación en Kardex <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model.live="tipoAjuste" 
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-medium">
                        <option value="AJUSTE_POSITIVO">➕ AJUSTE POSITIVO (Sobrante físico / Regularización)</option>
                        <option value="AJUSTE_NEGATIVO">➖ AJUSTE NEGATIVO (Merma / Faltante / Daño)</option>
                        <option value="BAJA_VENCIMIENTO">🛑 BAJA POR VENCIMIENTO (Retiro sanitario)</option>
                    </select>
                </div>

                <!-- Cantidad a Ajustar -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Cantidad de Unidades <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" 
                           step="1" 
                           wire:model="cantidadAjuste" 
                           min="1" 
                           class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 font-mono font-bold">
                    @error('cantidadAjuste') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Motivo / Justificación -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo / Justificación de Auditoría <span class="text-rose-500">*</span>
                    </label>
                    <textarea wire:model="motivoAjuste" 
                              rows="3" 
                              placeholder="Explique detalladamente la causa del ajuste o baja para registro en Kardex..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
                    @error('motivoAjuste') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Footer Modal -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" 
                            wire:click="closeAjusteModal" 
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl {{ $tipoAjuste === 'BAJA_VENCIMIENTO' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-900/20' : 'bg-teal-600 hover:bg-teal-700 shadow-teal-900/20' }} text-white text-sm font-medium transition-all shadow-sm active:scale-95 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="guardarAjuste">Confirmar y Asentar en Kardex</span>
                        <span wire:loading wire:target="guardarAjuste" class="flex items-center gap-1.5">
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
