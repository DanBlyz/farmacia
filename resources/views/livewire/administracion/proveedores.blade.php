<div>
    <x-slot name="header">
        Proveedores
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

    <!-- Tarjeta Única Unificada (Cabecera, Filtro/Buscador y Tabla) -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-200">
        
        <!-- 1. Cabecera y Botón Nuevo -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-600 flex items-center justify-center text-white shadow-md shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Listado de Proveedores</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Gestiona laboratorios, distribuidoras y socios de abastecimiento</p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('administracion.proveedores.crear')
                <button wire:click="create" 
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-md transition duration-150 flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Nuevo Proveedor</span>
                </button>
                @endpermiso
            </div>
        </div>

        <!-- 2. Barra de Filtros y Búsqueda en la misma fila -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
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
                        placeholder="Buscar por nombre, NIT o celular..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Tabla de Registros -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                        <th class="py-3.5 px-6">Proveedor / Representante</th>
                        <th class="py-3.5 px-6 w-32">NIT / CI</th>
                        <th class="py-3.5 px-6 w-40">Contacto / Celular</th>
                        <th class="py-3.5 px-6">Dirección / Ubicación</th>
                        <th class="py-3.5 px-6 text-right w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse ($proveedores as $proveedor)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                        {{ $proveedor->nombre }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-medium">
                                        {{ $proveedor->razon_social ?: 'Sin razón social registrada' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-mono font-bold text-xs text-purple-600 dark:text-purple-400 border border-slate-200 dark:border-slate-700">
                                    {{ $proveedor->nit ?: 'S/N' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-300 font-medium">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    <span>{{ $proveedor->celular ?: 'No registrado' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2 max-w-xs truncate">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span class="truncate">{{ $proveedor->direccion ?: 'Sin dirección registrada' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @permiso('administracion.proveedores.editar')
                                    <button wire:click="edit({{ $proveedor->id }})" 
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950 hover:text-teal-600 dark:hover:text-teal-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Editar Proveedor">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    @endpermiso

                                    @permiso('administracion.proveedores.eliminar')
                                    <button type="button"
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Está seguro de eliminar este proveedor?',
                                                text: 'Se eliminará a {{ addslashes($proveedor->nombre) }} del catálogo. Esta acción registrará auditoría.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'delete',
                                                componentId: '{{ $this->getId() }}',
                                                method: 'delete',
                                                params: [{{ $proveedor->id }}]
                                            })" 
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950 hover:text-rose-600 dark:hover:text-rose-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Eliminar Proveedor">
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
                                        <svg class="w-7 h-7 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No se encontraron proveedores</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">No hay coincidencias con tu búsqueda actual o aún no se han creado registros en el catálogo.</p>
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

        <!-- 4. Paginación inferior -->
        @if($proveedores->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                {{ $proveedores->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Crear / Editar Proveedor (Compacto y Centrado en pantalla) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closeModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-xl w-full border border-slate-200 dark:border-slate-800 z-10 my-auto">
                <form wire:submit="save">
                    <!-- Cabecera del Modal -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $proveedorId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                {{ $proveedorId ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                            </h3>
                        </div>
                        <button 
                            wire:click="closeModal" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Cuerpo del Modal -->
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nombre (nombre y apellidos) -->
                            <div class="sm:col-span-2">
                                <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Nombre y Apellidos / Contacto <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nombre" 
                                       wire:model="nombre" 
                                       placeholder="Ej: Lic. Carlos Mendoza / Laboratorio Bagó" 
                                       autofocus
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('nombre') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('nombre')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Razón Social -->
                            <div>
                                <label for="razon_social" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Razón Social
                                </label>
                                <input type="text" 
                                       id="razon_social" 
                                       wire:model="razon_social" 
                                       placeholder="Ej: Laboratorios Farmacéuticos S.A." 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('razon_social') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('razon_social')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NIT -->
                            <div>
                                <label for="nit" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    NIT / CI
                                </label>
                                <input type="text" 
                                       id="nit" 
                                       wire:model="nit" 
                                       placeholder="Ej: 1029384756" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('nit') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('nit')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Celular -->
                            <div class="sm:col-span-2">
                                <label for="celular" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Teléfono / Celular
                                </label>
                                <input type="text" 
                                       id="celular" 
                                       wire:model="celular" 
                                       placeholder="Ej: +591 71234567" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('celular') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('celular')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Dirección / Ubicación Física
                            </label>
                            <textarea id="direccion" 
                                      wire:model="direccion" 
                                      rows="2"
                                      placeholder="Ej: Av. Ballivián #550, Calacoto, La Paz" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('direccion') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all resize-none"></textarea>
                            @error('direccion')
                                <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3.5 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-800 dark:text-purple-300 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <p class="font-bold">Información de Abastecimiento</p>
                                <p class="mt-0.5 opacity-90">El proveedor estará disponible para el registro de compras, ingresos a almacén y notas de recepción.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pie del Modal con botones de acción -->
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" 
                                wire:click="closeModal" 
                                class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                            Cancelar
                        </button>

                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-save"></i>
                                <span>Guardar</span>
                            </span>
                            <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Procesando...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
