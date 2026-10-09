<div>
    <x-slot name="header">
        Sucursales
    </x-slot>

    <!-- Mensaje de Notificación Flash -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 4000)"
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
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
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
        
        <!-- 1. Cabecera y Botón Nuevo -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Listado de Sucursales</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Gestiona los puntos de venta y farmacias físicas</p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('administracion.sucursales.crear')
                <button wire:click="create" 
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Nueva Sucursal</span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra de Filtros y Búsqueda (Rule 9) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-1">
            <!-- Izquierda: Paginación Dinámica -->
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                <select 
                    id="perPage" 
                    wire:model.live="perPage" 
                    class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
            </div>

            <!-- Derecha: Buscador Debounce -->
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por código, nombre o dirección..." 
                    class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                />
                @if ($search !== '')
                    <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times-circle text-xs"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- 3. Tabla de Registros Clásica (Enmarcada con bordes propios, sin abarcar todo el card) -->
        <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                        <th class="py-3 px-4 w-28">Código</th>
                        <th class="py-3 px-4">Nombre de la Sucursal</th>
                        <th class="py-3 px-4">Dirección / Ubicación</th>
                        <th class="py-3 px-4 w-40">Registro</th>
                        <th class="py-3 px-4 text-right w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse ($sucursales as $sucursal)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-600 dark:text-slate-300">
                                {{ $sucursal->codigo_sucursal }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                        {{ $sucursal->nombre }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2 max-w-md truncate">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span class="truncate">{{ $sucursal->direccion ?: 'Sin dirección registrada' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $sucursal->created_at ? $sucursal->created_at->format('d/m/Y') : 'N/D' }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $sucursal->created_at ? $sucursal->created_at->format('H:i') : '' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @permiso('administracion.sucursales.puntos-venta')
                                        <button wire:click="openPuntosVentaModal({{ $sucursal->id }})" 
                                                class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950 hover:text-teal-600 dark:hover:text-teal-400 transition-colors focus:outline-none cursor-pointer flex items-center"
                                                title="Ver Puntos de Venta (SIAT)">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                            <span class="hidden xl:inline ml-1 text-xs font-medium">Puntos de Venta</span>
                                        </button>
                                    @endpermiso

                                    @permiso('administracion.sucursales.editar')
                                        <button wire:click="edit({{ $sucursal->id }})" 
                                                class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950 hover:text-teal-600 dark:hover:text-teal-400 transition-colors focus:outline-none cursor-pointer"
                                                title="Editar Sucursal">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                    @endpermiso

                                    @permiso('administracion.sucursales.eliminar')
                                        <button @click="$dispatch('swal:confirm', {
                                                    title: '¿Eliminar sucursal?',
                                                    text: 'Se eliminará la sucursal \'{{ addslashes($sucursal->nombre) }}\'',
                                                    action: 'delete',
                                                    id: {{ $sucursal->id }}
                                                })" 
                                                type="button"
                                                class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950 hover:text-rose-600 dark:hover:text-rose-400 transition-colors focus:outline-none cursor-pointer"
                                                title="Eliminar Sucursal">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No se encontraron sucursales</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">No hay coincidencias con tu búsqueda actual o aún no se han creado sucursales en el sistema.</p>
                                    @if($search !== '')
                                        <button wire:click="$set('search', '')" class="mt-4 px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-medium transition-colors cursor-pointer">
                                            Limpiar filtro de búsqueda
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endempty
                </tbody>
            </table>
        </div>

        <!-- 4. Paginación Clásica -->
        @if($sucursales->hasPages())
            <div class="pt-2">
                {{ $sucursales->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Crear / Editar Sucursal (Compacto y Centrado en pantalla) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closeModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg border border-slate-200 dark:border-slate-800 z-10 my-auto">
                <form wire:submit="save">
                    <!-- Cabecera del Modal -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                                <i class="fas fa-store"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                    {{ $sucursalId ? 'Editar Datos de Sucursal' : 'Registrar Nueva Sucursal' }}
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $sucursalId ? 'Modifica los datos y ubicación de la sucursal' : 'Ingresa el código, nombre y dirección de la nueva sucursal' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    <!-- Cuerpo del Modal -->
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Código de Sucursal -->
                            <div class="sm:col-span-1">
                                <label for="codigo_sucursal" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Código <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="codigo_sucursal" 
                                       wire:model="codigo_sucursal" 
                                       placeholder="Codigo segun siat" 
                                       autofocus
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('codigo_sucursal') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('codigo_sucursal')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nombre de la Sucursal -->
                            <div class="sm:col-span-2">
                                <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Nombre de Sucursal <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nombre" 
                                       wire:model="nombre" 
                                       placeholder="Ej: Farmacia Central - Av. Principal" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('nombre') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('nombre')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Dirección / Ubicación -->
                        <div>
                            <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Dirección Física / Ubicación
                            </label>
                            <textarea id="direccion" 
                                      wire:model="direccion" 
                                      rows="3"
                                      placeholder="Ej: Av. 16 de Julio #1234, entre calle Bueno y Loayza, Zona Centro" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('direccion') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all resize-none"></textarea>
                            @error('direccion')
                                <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3.5 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-800 dark:text-purple-300 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <p class="font-bold">Información de Sucursal</p>
                                <p class="mt-0.5 opacity-90">El código de sucursal se vinculará con las transacciones de facturación electrónica e inventario.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pie del Modal con botones de acción -->
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" 
                                wire:click="closeModal" 
                                class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition-colors">
                            Cancelar
                        </button>

                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="px-5 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-2xs transition-all duration-200 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                                <i class="fas fa-check text-xs"></i>
                                <span>Guardar</span>
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
    @endif

    <!-- Modal Listado de Puntos de Venta y SIAT -->
    @if ($showPuntosVentaModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" aria-labelledby="pv-modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closePuntosVentaModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-5xl border border-slate-200 dark:border-slate-800 z-10 my-auto">
                <!-- Cabecera del Modal -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100" id="pv-modal-title">
                                Puntos de Venta (Facturación en Línea SIAT)
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Sucursal: <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $selectedSucursalNombre }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closePuntosVentaModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                <!-- Barra de Acciones de Sucursal SIAT -->
                <div class="px-6 pt-5 pb-3 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/30 dark:bg-slate-800/20">
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Gestión Global SIAT de la Sucursal:</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Botón Registro Punto de Venta -->
                        <button type="button" 
                                wire:click="openRegistroPuntoVentaModal({{ $selectedSucursalId }})"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-semibold text-xs shadow-sm flex items-center gap-2 transition-all focus:outline-none disabled:opacity-50"
                                title="Registrar un nuevo punto de venta en SIAT para esta sucursal">
                            <svg wire:loading.remove wire:target="openRegistroPuntoVentaModal({{ $selectedSucursalId }})" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <svg wire:loading wire:target="openRegistroPuntoVentaModal({{ $selectedSucursalId }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Registro Punto Venta</span>
                        </button>

                        <!-- Botón Sincronización Punto de Ventas -->
                        <button type="button" 
                                wire:click="sincronizarPuntosVenta({{ $selectedSucursalId }})"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-semibold text-xs shadow-sm flex items-center gap-2 transition-all focus:outline-none disabled:opacity-50">
                            <svg wire:loading.remove wire:target="sincronizarPuntosVenta({{ $selectedSucursalId }})" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <svg wire:loading wire:target="sincronizarPuntosVenta({{ $selectedSucursalId }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Sincronización Punto de Ventas</span>
                        </button>

                        <!-- Botón Cierre Operaciones de Sistema -->
                        <button type="button" 
                                wire:click="cierreOperacionesSistema({{ $selectedSucursalId }})"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-2 rounded-xl bg-[#c72044] hover:bg-[#a61b38] text-white font-semibold text-xs shadow-sm flex items-center gap-2 transition-all focus:outline-none disabled:opacity-50"
                                title="Cerrar operaciones del sistema con SIAT para esta sucursal">
                            <svg wire:loading.remove wire:target="cierreOperacionesSistema({{ $selectedSucursalId }})" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <svg wire:loading wire:target="cierreOperacionesSistema({{ $selectedSucursalId }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Cierre Operaciones de Sistema</span>
                        </button>
                    </div>
                </div>

                <!-- Cuerpo del Modal: Tabla de Puntos de Venta -->
                <div class="p-6 overflow-x-auto max-h-[70vh]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                                <th class="py-3 px-4 w-20">Código</th>
                                <th class="py-3 px-4">Nombre / Tipo</th>
                                <th class="py-3 px-4 w-48">Estado CUIS SIAT</th>
                                <th class="py-3 px-4 text-right">Acciones SIAT (CUIS y Sincronizaciones)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                            @forelse ($puntosVenta as $pv)
                                @php
                                    $cuisVigente = $cuisMap[$pv->id] ?? null;
                                    $hasValidCuis = $cuisVigente && \Carbon\Carbon::parse($cuisVigente->fecha_vigencia)->greaterThanOrEqualTo(now());
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ $pv->codigo }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ $pv->nombre }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $pv->tipo }} (Amb. {{ $pv->codigo_ambiente }})</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($hasValidCuis)
                                            <div class="inline-flex flex-col px-3 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400">
                                                <span class="text-xs font-bold font-mono">CUIS: {{ $cuisVigente->codigo }}</span>
                                                <span class="text-[10px] opacity-80">Vence: {{ \Carbon\Carbon::parse($cuisVigente->fecha_vigencia)->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-400">
                                                Sin CUIS Vigente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <!-- Botón Crear / Renovar CUIS -->
                                            <button wire:click="crearCuis({{ $pv->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5 transition-all focus:outline-none disabled:opacity-50">
                                                <svg wire:loading.remove wire:target="crearCuis({{ $pv->id }})" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                                <svg wire:loading wire:target="crearCuis({{ $pv->id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span>{{ $hasValidCuis ? 'Renovar CUIS' : 'Crear CUIS' }}</span>
                                            </button>

                                            <!-- Botón Sincronizar Actividades -->
                                            <button wire:click="sincronizarActividades({{ $pv->id }})"
                                                    wire:loading.attr="disabled"
                                                    @if (!$hasValidCuis) disabled title="Se requiere un CUIS vigente del SIAT" @endif
                                                    class="px-3 py-1.5 rounded-xl {{ $hasValidCuis ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-60' }} font-semibold text-xs flex items-center gap-1.5 transition-all focus:outline-none">
                                                <svg wire:loading.remove wire:target="sincronizarActividades({{ $pv->id }})" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                                <svg wire:loading wire:target="sincronizarActividades({{ $pv->id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span>Sincronizar Actividades</span>
                                            </button>

                                            <!-- Botón Sincronizar Producto Servicio -->
                                            <button wire:click="sincronizarProductosServicios({{ $pv->id }})"
                                                    wire:loading.attr="disabled"
                                                    @if (!$hasValidCuis) disabled title="Se requiere un CUIS vigente del SIAT" @endif
                                                    class="px-3 py-1.5 rounded-xl {{ $hasValidCuis ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-60' }} font-semibold text-xs flex items-center gap-1.5 transition-all focus:outline-none">
                                                <svg wire:loading.remove wire:target="sincronizarProductosServicios({{ $pv->id }})" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                                <svg wire:loading wire:target="sincronizarProductosServicios({{ $pv->id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span>Sincronizar Producto Servicio</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-slate-400 dark:text-slate-500 text-sm">
                                        No hay puntos de venta registrados en esta sucursal.
                                    </td>
                                </tr>
                            @endempty
                        </tbody>
                    </table>
                </div>

                <!-- Pie del Modal -->
                <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end">
                    <button type="button" wire:click="closePuntosVentaModal" class="px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Registro Punto Venta SIAT -->
    @if($showRegistroPuntoVentaModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" wire:click="closeRegistroPuntoVentaModal"></div>

            <!-- Modal Content -->
            <div class="relative max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden z-10 animate-in fade-in zoom-in-95 duration-200">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                Registro de Punto de Venta SIAT
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Sucursal: <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $selectedSucursalNombre }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeRegistroPuntoVentaModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="guardarRegistroPuntoVenta">
                    <div class="p-6 space-y-4">
                        <!-- Nombre del Punto de Venta -->
                        <div>
                            <label for="newPuntoVentaNombre" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Nombre del Punto de Venta <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="newPuntoVentaNombre" id="newPuntoVentaNombre" placeholder="Ej: Ventanilla Principal / Caja 2" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            @error('newPuntoVentaNombre')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción del Punto de Venta -->
                        <div>
                            <label for="newPuntoVentaDescripcion" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Descripción <span class="text-rose-500">*</span>
                            </label>
                            <textarea wire:model="newPuntoVentaDescripcion" id="newPuntoVentaDescripcion" rows="2" placeholder="Ej: Punto de Venta para cobros rápidos" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"></textarea>
                            @error('newPuntoVentaDescripcion')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tipo de Punto de Venta SIAT -->
                        <div>
                            <label for="newPuntoVentaTipoId" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Tipo de Punto de Venta (SIAT) <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="newPuntoVentaTipoId" id="newPuntoVentaTipoId" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                <option value="">Seleccione el Tipo de Punto de Venta</option>
                                @foreach($tiposPuntoVenta as $tipo)
                                    <option value="{{ $tipo->codigo_clasificador }}">{{ $tipo->codigo_clasificador }} - {{ $tipo->descripcion }}</option>
                                @endforeach
                            </select>
                            @error('newPuntoVentaTipoId')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeRegistroPuntoVentaModal" class="px-4 py-2.5 rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="guardarRegistroPuntoVenta" class="px-5 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-2xs flex items-center gap-2 transition-all focus:outline-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="guardarRegistroPuntoVenta" class="flex items-center gap-2">
                                <i class="fas fa-check text-xs"></i>
                                <span>Registrar en SIAT</span>
                            </span>
                            <span wire:loading wire:target="guardarRegistroPuntoVenta" class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin text-xs"></i>
                                <span>Registrando...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        window.addEventListener('confirm-delete-sucursal', (event) => {
            const data = event.detail;
            window.showConfirmSwal(
                '¿Confirmar eliminación?',
                `Estás a punto de eliminar la sucursal <b>"${data.nombre}"</b> del catálogo.<br><span class="text-xs text-slate-400 dark:text-slate-500">Esta acción es reversible mediante Soft Deletes.</span>`,
                () => @this.delete(data.id)
            );
        });

        window.addEventListener('sucursal-deleted', (event) => {
            window.showToastSwal('¡Eliminado!', event.detail.message, 'success');
        });

        window.addEventListener('swal-alert', (event) => {
            const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            Swal.fire({
                title: data.title || (data.icon === 'error' ? 'Atención SIAT' : '¡Éxito!'),
                html: data.message,
                icon: data.icon || 'info',
                confirmButtonColor: data.icon === 'error' ? '#e11d48' : '#0d9488',
                confirmButtonText: '<svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Entendido',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                customClass: {
                    popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans p-6 text-left',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-md transition-all'
                }
            });
        });
    });
</script>
@endpush
