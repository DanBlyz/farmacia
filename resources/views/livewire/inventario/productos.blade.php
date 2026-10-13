<div>
    <x-slot name="header">
        Catálogo de Medicamentos e Inventario
    </x-slot>

    <!-- Notificación Flash -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
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

    <!-- Tarjeta Principal (Estilo Clásico: contenido con padding y tabla enmarcada) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
        
        <!-- 1. Cabecera y Botón Nuevo -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                    <i class="fas fa-pills text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">
                        Catálogo de Medicamentos y Productos
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Gestión farmacéutica de productos, trazabilidad FEFO de lotes y homologación con SIAT
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('inventario.productos.crear')
                <button type="button" 
                        wire:click="create" 
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Nuevo Medicamento</span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra Superior de Control y Filtros (Regla 9) -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 pt-1">
            <!-- Selector de Paginación Dinámica (10, 25, 50, 100) -->
            <div class="md:col-span-2 flex items-center gap-2">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Mostrar</span>
                <select wire:model.live="perPage" 
                        class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs outline-none cursor-pointer">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">filas</span>
            </div>

            <!-- Filtro por Proveedor / Distribuidor -->
            <div class="md:col-span-3">
                <select wire:model.live="filtroProveedorId"
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-2.5 shadow-2xs outline-none cursor-pointer">
                    <option value="">Todos los Proveedores</option>
                    @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->razon_social ?: $prov->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Sucursal -->
            <div class="md:col-span-3">
                <select wire:model.live="filtroSucursalId"
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-2.5 shadow-2xs outline-none cursor-pointer">
                    <option value="">Todas las Sucursales</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buscador en Tiempo Real -->
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por medicamento, principio activo, composición o proveedor..." 
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

        <!-- 3. Tabla de Medicamentos Clásica (Enmarcada con bordes propios) -->
        <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                        <th class="py-3 px-4 w-32 text-center">Código / Barra</th>
                        <th class="py-3 px-4">Medicamento & Composición</th>
                        <th class="py-3 px-4">Proveedor & Presentación</th>
                        <th class="py-3 px-4 text-right">Precio Venta</th>
                        <th class="py-3 px-4 text-center">Stock Físico (FEFO)</th>
                        <th class="py-3 px-4 text-center w-28">Estado</th>
                        <th class="py-3 px-4 text-center w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse($productos as $item)
                        @php
                            $stockTotal = $item->stockTotal();
                            $loteProximo = $item->lotes()->where('estado', 'ACTIVO')->fefo()->first();
                            $esVencido = $loteProximo && $loteProximo->esVencido();
                            $esPorVencer = $loteProximo && $loteProximo->esProximoAVencer(60);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Código / Barra -->
                            <td class="py-3 px-4 text-center">
                                <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 block">
                                    {{ $item->codigo }}
                                </span>
                                @if($item->codigo_barras)
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">
                                        <i class="fas fa-barcode mr-1"></i>{{ $item->codigo_barras }}
                                    </span>
                                @endif
                            </td>

                            <!-- Medicamento & Composición Farmacológica -->
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800 dark:text-white flex items-center gap-1.5">
                                    <span>{{ $item->nombre }}</span>
                                    @if($item->requiere_receta)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" title="Requiere Receta Médica">
                                            Rx
                                        </span>
                                    @endif
                                </div>
                                @if($item->principio_activo)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-sm mt-0.5">
                                        <i class="fas fa-flask text-[10px] mr-1 text-teal-600"></i><span class="font-medium text-slate-600 dark:text-slate-300">DCI:</span> {{ $item->principio_activo }}
                                    </div>
                                @endif
                                @if($item->composicion)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-sm mt-0.5">
                                        <i class="fas fa-dna text-[10px] mr-1 text-cyan-600"></i><span class="font-medium text-slate-600 dark:text-slate-300">Comp:</span> {{ $item->composicion }}
                                    </div>
                                @endif
                                @if($item->accion_terapeutica)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-sm mt-0.5">
                                        <i class="fas fa-stethoscope text-[10px] mr-1 text-emerald-600"></i><span class="font-medium text-slate-600 dark:text-slate-300">Acción:</span> {{ $item->accion_terapeutica }}
                                    </div>
                                @endif
                                @if($item->codigo_producto_sin)
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                                        SIN: {{ $item->codigo_producto_sin }}
                                    </div>
                                @endif
                            </td>

                            <!-- Proveedor & Presentación -->
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300">
                                <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <i class="fas fa-truck text-[10px] text-slate-400"></i>
                                    <span>{{ $item->proveedor?->razon_social ?: ($item->proveedor?->nombre ?: ($item->laboratorio ?: 'Sin Proveedor')) }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $item->presentacion ?: $item->unidad }}
                                    @if($item->gramaje)
                                        • {{ $item->gramaje }}
                                    @endif
                                    @if($item->unidades_por_caja > 1)
                                        <span class="text-slate-400">({{ $item->unidades_por_caja }} unid/caja)</span>
                                    @endif
                                </div>
                                @if($item->ubicacion_estante)
                                    <div class="text-[10px] text-teal-600 dark:text-teal-400 font-mono mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $item->ubicacion_estante }}
                                    </div>
                                @endif
                            </td>

                            <!-- Precio Venta -->
                            <td class="py-3 px-4 text-right">
                                <span class="font-bold text-slate-800 dark:text-slate-100">
                                    Bs. {{ number_format($item->precio_venta, 2) }}
                                </span>
                                <span class="block text-[10px] text-slate-400">
                                    Costo: Bs. {{ number_format($item->precio_compra, 2) }}
                                </span>
                            </td>

                            <!-- Stock Físico (FEFO) y Semáforo -->
                            <td class="py-3 px-4 text-center">
                                <div class="font-bold text-xs {{ $stockTotal <= $item->minimo_stock ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                                    {{ number_format($stockTotal, 0) }} {{ $item->unidad }}
                                </div>

                                <!-- Semáforo de Vencimiento del lote próximo -->
                                @if($loteProximo)
                                    @if($esVencido)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 mt-1" title="Lote {{ $loteProximo->numero_lote }} VENCIDO">
                                            <i class="fas fa-times-circle text-[9px]"></i> Vencido
                                        </span>
                                    @elseif($esPorVencer)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 mt-1" title="Lote {{ $loteProximo->numero_lote }} vence en {{ Carbon::parse($loteProximo->fecha_vencimiento)->diffForHumans() }}">
                                            <i class="fas fa-exclamation-triangle text-[9px]"></i> Próx. a vencer
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 mt-1" title="Lote {{ $loteProximo->numero_lote }} en estado óptimo">
                                            <i class="fas fa-check-circle text-[9px]"></i> Vigente
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[10px] text-slate-400 block mt-1">Sin lotes</span>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="py-3 px-4 text-center">
                                @if($item->estado === 'ACTIVO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Ver Lotes FEFO -->
                                    <button type="button" 
                                            wire:click="verLotes({{ $item->id }})" 
                                            title="Ver Lotes y Trazabilidad FEFO"
                                            class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-900/50 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-boxes text-xs"></i>
                                    </button>

                                    <!-- Editar -->
                                    @permiso('inventario.productos.editar')
                                    <button type="button" 
                                            wire:click="edit({{ $item->id }})" 
                                            title="Editar Medicamento"
                                            class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    @endpermiso

                                    <!-- Eliminar -->
                                    @permiso('inventario.productos.eliminar')
                                    <button type="button" 
                                            @click="$dispatch('swal:confirm', { 
                                                title: '¿Eliminar Medicamento?', 
                                                text: 'Esta acción dará de baja el medicamento {{ $item->nombre }} del catálogo.', 
                                                id: {{ $item->id }}, 
                                                action: 'delete' 
                                            })" 
                                            title="Eliminar Medicamento"
                                            class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-trash-alt text-xs"></i>
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
                                        <i class="fas fa-pills text-lg"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        @if($search !== '')
                                            No se encontraron medicamentos para "{{ $search }}"
                                        @else
                                            No hay medicamentos registrados en el catálogo
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        @if($search !== '')
                                            Intente buscar por otro término o limpie el filtro.
                                        @else
                                            Haga clic en "Nuevo Medicamento" para comenzar a registrar su inventario farmacéutico.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 4. Paginación Desacoplada (Regla 9) -->
        @if ($productos->hasPages())
            <div class="pt-2">
                {{ $productos->links() }}
            </div>
        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL DE MEDICAMENTO (Creación / Edición) -->
    <!-- ========================================================================= -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay oscuro -->
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 transition-opacity" 
             wire:click="closeModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200 dark:border-slate-800">
                
                <!-- Cabecera del Modal (Regla 7: Sobria, sin gradientes) -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 px-6 py-4 bg-slate-50 dark:bg-slate-800/60">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-600 flex items-center justify-center text-white shrink-0">
                            <i class="fas {{ $productoId ? 'fa-edit' : 'fa-plus' }} text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white leading-tight">
                                {{ $productoId ? 'Editar Medicamento / Producto' : 'Dar de Alta Nuevo Medicamento' }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Ingrese los datos farmacéuticos y parámetros de homologación con el SIAT
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            wire:click="closeModal" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Barra de Pestañas del Modal -->
                <div class="flex border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 px-6 pt-2 gap-2 text-xs">
                    <button type="button" 
                            wire:click="$set('modalTab', 'ficha')"
                            class="px-4 py-2 font-semibold border-b-2 transition-all cursor-pointer {{ $modalTab === 'ficha' ? 'border-teal-600 text-teal-600 dark:text-teal-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
                        <i class="fas fa-pills mr-1.5"></i> Ficha Farmacéutica
                    </button>
                    <button type="button" 
                            wire:click="$set('modalTab', 'siat')"
                            class="px-4 py-2 font-semibold border-b-2 transition-all cursor-pointer {{ $modalTab === 'siat' ? 'border-teal-600 text-teal-600 dark:text-teal-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
                        <i class="fas fa-file-invoice mr-1.5"></i> Homologación SIAT
                    </button>
                    @if(! $productoId)
                        <button type="button" 
                                wire:click="$set('modalTab', 'lote')"
                                class="px-4 py-2 font-semibold border-b-2 transition-all cursor-pointer {{ $modalTab === 'lote' ? 'border-teal-600 text-teal-600 dark:text-teal-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
                            <i class="fas fa-boxes mr-1.5"></i> Lote Inicial (Opcional)
                        </button>
                    @endif
                </div>

                <!-- Cuerpo del Formulario -->
                <form wire:submit.prevent="save">
                    <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-4">
                        
                        <!-- PESTAÑA 1: FICHA FARMACÉUTICA -->
                        <div x-show="$wire.modalTab === 'ficha'" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <!-- Nombre Comercial -->
                                <div class="sm:col-span-8">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Nombre Comercial <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           wire:model="nombre" 
                                           placeholder="Ej. Paracetamol 500mg, Amoxicilina 1g..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('nombre') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Código Interno -->
                                <div class="sm:col-span-4">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Código Interno <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           wire:model="codigo" 
                                           placeholder="MED-00001" 
                                           class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('codigo') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <!-- Principio Activo -->
                                <div class="sm:col-span-6">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Principio Activo (DCI)
                                    </label>
                                    <input type="text" 
                                           wire:model="principio_activo" 
                                           placeholder="Ej. Paracetamol, Ibuprofeno..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                </div>

                                <!-- Código de Barras -->
                                <div class="sm:col-span-6">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Código de Barras (EAN / UPC)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-barcode text-xs"></i>
                                        </div>
                                        <input type="text" 
                                               wire:model="codigo_barras" 
                                               placeholder="Escanear con pistola..." 
                                               class="w-full pl-9 pr-3 py-2 text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Proveedor / Distribuidor -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Proveedor / Laboratorio Distribuidor
                                </label>
                                <select wire:model="proveedor_id" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none cursor-pointer">
                                    <option value="">Seleccione el Proveedor o Laboratorio (Opcional)...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}">
                                            {{ $prov->razon_social ? $prov->razon_social.' ('.$prov->nombre.')' : $prov->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor_id') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <!-- Composición Farmacológica -->
                                <div class="sm:col-span-6">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Composición Farmacológica
                                    </label>
                                    <input type="text" 
                                           wire:model="composicion" 
                                           placeholder="Ej. Cada comprimido contiene Paracetamol 500mg, Cafeína 50mg..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('composicion') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Acción Terapéutica -->
                                <div class="sm:col-span-6">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Acción Terapéutica / Indicación
                                    </label>
                                    <input type="text" 
                                           wire:model="accion_terapeutica" 
                                           placeholder="Ej. Analgésico, Antipirético, Antiinflamatorio..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('accion_terapeutica') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <!-- Presentación -->
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Presentación Comercial
                                    </label>
                                    <input type="text" 
                                           wire:model="presentacion" 
                                           placeholder="Caja x 100, Jarabe 120ml..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('presentacion') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Gramaje / Concentración -->
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Concentración / Gramaje
                                    </label>
                                    <input type="text" 
                                           wire:model="gramaje" 
                                           placeholder="500 mg, 1 g, 200 mg/5ml..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('gramaje') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Unidad de Dispensación -->
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Unidad de Dispensación
                                    </label>
                                    <input type="text" 
                                           wire:model="unidad" 
                                           placeholder="UNIDAD, COMPRIMIDO..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('unidad') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Unidades por Caja -->
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Unidades por Empaque
                                    </label>
                                    <input type="number" 
                                           wire:model="unidades_por_caja" 
                                           min="1" 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    @error('unidades_por_caja') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Precios y Stock de Seguridad -->
                            <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="fas fa-tag text-teal-600"></i> Precios y Parámetros de Stock
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Precio de Compra (Bs.) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" 
                                               step="0.01" 
                                               wire:model="precio_compra" 
                                               class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                        @error('precio_compra') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Precio de Venta (Bs.) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" 
                                               step="0.01" 
                                               wire:model="precio_venta" 
                                               class="w-full text-xs font-bold text-teal-700 dark:text-teal-400 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                        @error('precio_venta') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Stock Mínimo Alerta <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" 
                                               step="1" 
                                               wire:model="minimo_stock" 
                                               class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones Especiales: Ubicación y Receta -->
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1">
                                <div class="sm:col-span-8">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Ubicación Física en Estante / Gaveta
                                    </label>
                                    <input type="text" 
                                           wire:model="ubicacion_estante" 
                                           placeholder="Ej. Estante A-2, Vitrina 1..." 
                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                </div>

                                <div class="sm:col-span-4 flex items-center pt-5">
                                    <label class="relative flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model="requiere_receta" 
                                               class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 dark:border-slate-700 cursor-pointer">
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            Requiere Receta Médica (Rx)
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 2: HOMOLOGACIÓN SIAT (SIN BOLIVIA) -->
                        <div x-show="$wire.modalTab === 'siat'" class="space-y-4">
                            <div class="p-3.5 bg-teal-50 dark:bg-teal-950/30 rounded-xl border border-teal-200 dark:border-teal-800/50 text-xs text-teal-800 dark:text-teal-300">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Homologación Fiscal:</strong> Para emitir facturas válidas en la Modalidad Computarizada en Línea, todo producto debe estar asociado al código de actividad/producto del SIN y su unidad de medida clasificada.
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Código de Producto y Servicio SIN
                                </label>
                                <input type="text" 
                                       wire:model="codigo_producto_sin" 
                                       placeholder="Código del catálogo SIN (ej. 85123, 85120...)" 
                                       class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                <span class="text-[11px] text-slate-400 block mt-1">
                                    Consulte los códigos descargados en el apartado <strong class="text-teal-600">Sincronización -> Catálogos (Productos y Servicios)</strong>.
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Unidad de Medida Oficial SIN
                                </label>
                                <select wire:model="unidad_medida_sin" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none cursor-pointer">
                                    <option value="">Seleccione Unidad de Medida Clasificada...</option>
                                    @foreach($unidadesSin as $u)
                                        <option value="{{ $u->codigo_clasificador }}">
                                            {{ $u->codigo_clasificador }} - {{ $u->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- PESTAÑA 3: LOTE INICIAL (OPCIONAL) -->
                        @if(! $productoId)
                        <div x-show="$wire.modalTab === 'lote'" class="space-y-4">
                            <div class="flex items-center gap-2 mb-3">
                                <input type="checkbox" 
                                       id="checkLoteInicial" 
                                       wire:model.live="crearLoteInicial" 
                                       class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 dark:border-slate-700 cursor-pointer">
                                <label for="checkLoteInicial" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                                    Registrar primer lote físico de ingreso inmediato para este medicamento
                                </label>
                            </div>

                            @if($crearLoteInicial)
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- Sucursal de Ingreso -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Sucursal Destino <span class="text-rose-500">*</span>
                                            </label>
                                            <select wire:model="sucursal_lote_id" 
                                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none cursor-pointer">
                                                @foreach($sucursales as $suc)
                                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Número de Lote -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Número de Lote <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" 
                                                   wire:model="numero_lote_inicial" 
                                                   placeholder="Ej. LOT-2026-A1" 
                                                   class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                            @error('numero_lote_inicial') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- Fecha de Vencimiento -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Fecha de Vencimiento <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="date" 
                                                   wire:model="fecha_vencimiento_inicial" 
                                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                            @error('fecha_vencimiento_inicial') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Cantidad Inicial -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Cantidad Inicial de Entrada <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="number" 
                                                   step="1" 
                                                   wire:model="stock_inicial" 
                                                   placeholder="Ej. 100" 
                                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 py-2 px-3 outline-none">
                                            @error('stock_inicial') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endif

                    </div>

                    <!-- Pie del Modal (Acciones) -->
                    <div class="bg-slate-50 dark:bg-slate-800/60 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" 
                                wire:click="closeModal" 
                                class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/60 transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                                <i class="fas fa-save text-xs"></i>
                                <span>{{ $productoId ? 'Guardar Cambios' : 'Registrar Medicamento' }}</span>
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin text-xs"></i>
                                <span>Guardando...</span>
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL DE LOTES DEL PRODUCTO (Trazabilidad FEFO) -->
    <!-- ========================================================================= -->
    @if($showLotesModal && $selectedProducto)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-lotes" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 transition-opacity" 
             wire:click="closeLotesModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-600 flex items-center justify-center text-white shrink-0">
                            <i class="fas fa-boxes text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white leading-tight">
                                Lotes de: {{ $selectedProducto->nombre }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Ordenados estrictamente bajo el método FEFO (Vencimiento más próximo primero)
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeLotesModal" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase font-bold text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">N° Lote</th>
                                <th class="py-2.5 px-3">Sucursal</th>
                                <th class="py-2.5 px-3 text-center">Vencimiento</th>
                                <th class="py-2.5 px-3 text-center">Stock</th>
                                <th class="py-2.5 px-3 text-center">Semáforo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($selectedProducto->lotes()->fefo()->get() as $lote)
                                <tr>
                                    <td class="py-2.5 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ $lote->numero_lote }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                        {{ $lote->sucursal?->nombre }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono">
                                        {{ Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold">
                                        {{ number_format($lote->stock_actual, 0) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($lote->esVencido())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                🔴 Caducado
                                            </span>
                                        @elseif($lote->esProximoAVencer(60))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                🟠 Próx. Vencer
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                🟢 Óptimo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        No hay lotes registrados para este medicamento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" 
                            wire:click="closeLotesModal" 
                            class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            window.addEventListener('swal-alert', (event) => {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                if (window.Swal && data) {
                    Swal.fire({
                        icon: data.icon || 'info',
                        title: data.title || '',
                        text: data.message || '',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#0d9488',
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                    });
                }
            });

            window.addEventListener('swal:confirm', (event) => {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                if (window.Swal && data) {
                    Swal.fire({
                        icon: 'warning',
                        title: data.title || '¿Está seguro?',
                        text: data.text || '',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call(data.action, data.id);
                        }
                    });
                }
            });
        });
    </script>
    @endpush
</div>
