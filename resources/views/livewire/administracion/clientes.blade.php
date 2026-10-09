<div>
    <x-slot name="header">
        Clientes
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
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Listado de Clientes</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Gestiona los pacientes y compradores habituales de la farmacia</p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('administracion.clientes.crear')
                <button wire:click="create" 
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-md transition duration-150 flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Nuevo Cliente</span>
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
                        placeholder="Buscar por nombre, CI, NIT o celular..." 
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
                        <th class="py-3.5 px-6">Cliente</th>
                        <th class="py-3.5 px-6 w-36">CI</th>
                        <th class="py-3.5 px-6 w-36">NIT</th>
                        <th class="py-3.5 px-6 w-40">Celular</th>
                        <th class="py-3.5 px-6">Correo Electrónico</th>
                        <th class="py-3.5 px-6 text-right w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse ($clientes as $cliente)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                        {{ trim($cliente->nombres . ' ' . $cliente->ap_paterno . ' ' . $cliente->ap_materno) }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-medium">
                                        {{ $cliente->razon_social ?: 'Factura a nombre particular' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-mono font-bold text-xs text-purple-600 dark:text-purple-400 border border-slate-200 dark:border-slate-700">
                                    {{ $cliente->cedula . ($cliente->complemento ? '-' . $cliente->complemento : '') }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-mono font-bold text-xs text-purple-600 dark:text-purple-400 border border-slate-200 dark:border-slate-700">
                                    {{ $cliente->nit ?: 'S/D' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-300 font-medium">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    <span>{{ $cliente->numero_celular ?: 'No registrado' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2 max-w-xs truncate">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    <span class="truncate">{{ $cliente->correo ?: 'Sin correo' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @permiso('administracion.clientes.editar')
                                    <button wire:click="edit({{ $cliente->id }})" 
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950 hover:text-teal-600 dark:hover:text-teal-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Editar Cliente">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    @endpermiso

                                    @permiso('administracion.clientes.eliminar')
                                    <button type="button"
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Está seguro de eliminar este cliente?',
                                                text: 'Se eliminará del catálogo activo. Esta acción registrará auditoría.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'delete',
                                                componentId: '{{ $this->getId() }}',
                                                method: 'delete',
                                                params: [{{ $cliente->id }}]
                                            })" 
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950 hover:text-rose-600 dark:hover:text-rose-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Eliminar Cliente">
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
                                        <svg class="w-7 h-7 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No se encontraron clientes</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">No hay coincidencias con tu búsqueda actual o aún no se han registrado clientes.</p>
                                    @if($search !== '')
                                        <button wire:click="$set('search', '')" class="mt-4 px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-medium transition-colors">
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
        @if($clientes->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Crear / Editar Cliente (Compacto y Centrado en pantalla) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closeModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl border border-slate-200 dark:border-slate-800 z-10 my-auto">
                <form wire:submit="save">
                    <!-- Cabecera del Modal -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $clienteId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                {{ $clienteId ? 'Editar Cliente' : 'Nuevo Cliente' }}
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
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Nombres -->
                            <div>
                                <label for="nombres" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Nombres <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nombres" 
                                       wire:model="nombres" 
                                       placeholder="Ej: Juan Diego" 
                                       autofocus
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('nombres') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('nombres')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Apellido Paterno -->
                            <div>
                                <label for="ap_paterno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Apellido Paterno
                                </label>
                                <input type="text" 
                                       id="ap_paterno" 
                                       wire:model="ap_paterno" 
                                       placeholder="Ej: Pérez" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('ap_paterno') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('ap_paterno')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Apellido Materno -->
                            <div>
                                <label for="ap_materno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Apellido Materno
                                </label>
                                <input type="text" 
                                       id="ap_materno" 
                                       wire:model="ap_materno" 
                                       placeholder="Ej: Gómez" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('ap_materno') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('ap_materno')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Cédula -->
                            <div>
                                <label for="cedula" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Cédula de Identidad (CI)
                                </label>
                                <input type="text" 
                                       id="cedula" 
                                       wire:model="cedula" 
                                       placeholder="Ej: 6543210" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('cedula') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('cedula')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Complemento -->
                            <div>
                                <label for="complemento" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Complemento
                                </label>
                                <input type="text" 
                                       id="complemento" 
                                       wire:model="complemento" 
                                       placeholder="Ej: 1A" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('complemento') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm font-mono uppercase text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('complemento')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NIT -->
                            <div>
                                <label for="nit" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    NIT
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
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Razón Social -->
                            <div>
                                <label for="razon_social" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Razón Social para Factura
                                </label>
                                <input type="text" 
                                       id="razon_social" 
                                       wire:model="razon_social" 
                                       placeholder="Ej: Pérez o Comercial Pérez" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('razon_social') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('razon_social')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Número de Celular -->
                            <div>
                                <label for="numero_celular" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Número de Celular
                                </label>
                                <input type="text" 
                                       id="numero_celular" 
                                       wire:model="numero_celular" 
                                       placeholder="Ej: +591 71234567" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('numero_celular') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('numero_celular')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="correo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Correo Electrónico
                            </label>
                            <input type="email" 
                                   id="correo" 
                                   wire:model="correo" 
                                   placeholder="Ej: cliente@correo.com (opcional para envío de facturas SIAT)" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('correo') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                            @error('correo')
                                <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3.5 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-800 dark:text-purple-300 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-purple-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <p class="font-bold">Facturación Electrónica en Línea (SIAT)</p>
                                <p class="mt-0.5 opacity-90">Los datos de CI, Complemento y NIT se vincularán al momento de emitir la factura para validar con el padrón del Servicio de Impuestos Nacionales.</p>
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
