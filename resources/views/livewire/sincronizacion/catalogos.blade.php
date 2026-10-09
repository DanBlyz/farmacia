<div>
    <x-slot name="header">
        Catálogos SIAT
    </x-slot>

    <!-- Notificación Flash de Éxito -->
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

    <!-- Notificación Flash de Error -->
    @if (session()->has('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 6000)"
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

    <!-- Tarjeta Principal (Estilo Clásico: contenido con padding y tabla enmarcada) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
        
        <!-- 1. Cabecera y Botones de Sincronización -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                    <i class="fas fa-database text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">
                        Catálogo de {{ $activeInfo['nombre'] }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Catálogos paramétricos oficiales homologados con Impuestos Nacionales (SIAT Bolivia)
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-auto">
                @permiso('sincronizacion.catalogos.sincronizar')
                <!-- Botón: Sincronizar Catálogo Activo -->
                <button type="button" 
                        wire:click="sincronizar('{{ $activeTab }}')"
                        wire:loading.attr="disabled"
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="sincronizar('{{ $activeTab }}')" class="flex items-center gap-2">
                        <i class="fas fa-sync-alt text-xs"></i>
                        <span>Sincronizar Este Catálogo</span>
                    </span>
                    <span wire:loading wire:target="sincronizar('{{ $activeTab }}')" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin text-xs"></i>
                        <span>Sincronizando...</span>
                    </span>
                </button>

                <!-- Botón: Sincronizar TODOS los Catálogos -->
                <button type="button" 
                        wire:click="sincronizarTodos"
                        wire:loading.attr="disabled"
                        title="Descarga y sincroniza las 11 tablas maestras desde SIAT"
                        class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="sincronizarTodos" class="flex items-center gap-2">
                        <i class="fas fa-layer-group text-xs text-teal-400"></i>
                        <span>Sincronizar Todo</span>
                    </span>
                    <span wire:loading wire:target="sincronizarTodos" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin text-xs"></i>
                        <span>Sincronizando Todo...</span>
                    </span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra de Navegación de Pestañas (Los 11 Catálogos Oficiales SIAT) -->
        <div class="flex flex-nowrap overflow-x-auto border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-xl p-1 hide-scrollbar gap-1">
            @foreach($tabs as $key => $tab)
                <button type="button"
                    wire:click="cambiarTab('{{ $key }}')"
                    class="px-3.5 py-2 text-xs whitespace-nowrap rounded-lg transition-all focus:outline-none cursor-pointer {{ $activeTab === $key ? 'bg-white dark:bg-slate-900 text-teal-600 dark:text-teal-400 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 font-medium' }}">
                    {{ $tab['nombre'] }}
                </button>
            @endforeach
        </div>

        <!-- 3. Barra Superior de Control (Regla 9: Selector de Registros + Buscador en Tiempo Real) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-1">
            <!-- Lado Izquierdo: Selector de Paginación Dinámica (10, 25, 50, 100) -->
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

            <!-- Lado Derecho: Buscador en Tiempo Real -->
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por código o descripción..." 
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

        <!-- 4. Tabla de Registros Clásica (Enmarcada con bordes propios, sin abarcar todo el card) -->
        <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                        @if($activeTab === 'ProductoServicio')
                            <th class="py-3 px-4 w-36 text-center">Código Producto</th>
                            <th class="py-3 px-4 w-36 text-center">Código Actividad</th>
                            <th class="py-3 px-4">Descripción Producto / Servicio</th>
                        @elseif($activeTab === 'DependeActividad')
                            <th class="py-3 px-4 w-36 text-center">Código CAEB</th>
                            <th class="py-3 px-4 w-28 text-center">Tipo</th>
                            <th class="py-3 px-4">Descripción Actividad Económica</th>
                        @elseif($activeTab === 'LeyendaFactura')
                            <th class="py-3 px-4 w-36 text-center">Código Actividad</th>
                            <th class="py-3 px-4">Leyenda Tributaria Oficial SIN</th>
                        @else
                            <th class="py-3 px-4 w-36 text-center">Código Clasificador</th>
                            <th class="py-3 px-4">Descripción Oficial SIN</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            @if($activeTab === 'ProductoServicio')
                                <td class="py-3 px-4 text-center font-mono">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $item->codigo_producto }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-slate-500 dark:text-slate-400">
                                    {{ $item->codigo_actividad }}
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $item->descripcion_producto }}
                                </td>
                            @elseif($activeTab === 'DependeActividad')
                                <td class="py-3 px-4 text-center font-mono">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $item->codigo_caeb }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-slate-500 dark:text-slate-400">
                                    {{ $item->tipo_actividad ?? '1' }}
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $item->descripcion }}
                                </td>
                            @elseif($activeTab === 'LeyendaFactura')
                                <td class="py-3 px-4 text-center font-mono">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $item->codigo_actividad ?: 'General' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-slate-700 dark:text-slate-300 italic">
                                    "{{ $item->descripcion_leyenda }}"
                                </td>
                            @else
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $item->{$activeInfo['col_codigo']} }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $item->{$activeInfo['col_desc'] ?? 'descripcion'} }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array($activeTab, ['ProductoServicio', 'DependeActividad']) ? 3 : 2 }}" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                        <i class="fas fa-search text-lg"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        @if($search !== '')
                                            No se encontraron registros que coincidan con "{{ $search }}"
                                        @else
                                            No hay registros sincronizados en este catálogo
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        @if($search !== '')
                                            Intente con otro término de búsqueda o limpie el filtro.
                                        @else
                                            Presione el botón "Sincronizar Este Catálogo" o "Sincronizar Todo" para descargar los datos oficiales desde el Servicio de Impuestos Nacionales.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 5. Pie de Tabla: Paginación Clásica -->
        @if ($items->hasPages())
            <div class="pt-2">
                {{ $items->links() }}
            </div>
        @endif

    </div>

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
        });
    </script>
    @endpush
</div>
