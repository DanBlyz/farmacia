<div>
    <x-slot name="header">
        Usuarios
    </x-slot>

    <!-- Tarjeta Principal (Estilo Clásico: contenido con padding y tabla enmarcada) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-5 transition-all duration-200">
        
        <!-- 1. Cabecera y Botón Nuevo -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-sm shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Listado de Usuarios</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Administra el personal, sus roles de acceso y sucursal asignada</p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                @permiso('administracion.usuarios.crear')
                <button wire:click="create" 
                        class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition duration-150 flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Nuevo Usuario</span>
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
                    placeholder="Buscar por nombre, CI, email o celular..." 
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
                        <th class="py-3 px-4">Colaborador</th>
                        <th class="py-3 px-4 w-32">Rol</th>
                        <th class="py-3 px-4 w-40">Sucursal</th>
                        <th class="py-3 px-4 w-32">Identificación</th>
                        <th class="py-3 px-4">Dirección</th>
                        <th class="py-3 px-4 text-right w-44">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs sm:text-sm">
                    @forelse ($usuarios as $usuario)
                        @php
                            $nombreVisible = trim($usuario->nombres . ' ' . $usuario->ap_paterno . ' ' . $usuario->ap_materno);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-teal-600 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm">
                                        {{ strtoupper(substr($nombreVisible ?: $usuario->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                            {{ $nombreVisible ?: $usuario->name }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-medium">
                                            {{ $usuario->email }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $usuario->rol ? $usuario->rol->nombre : 'Sin Rol' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                    {{ $usuario->puntoVenta && $usuario->puntoVenta->sucursal ? $usuario->puntoVenta->sucursal->nombre : 'Sin Asignar' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                <div class="flex flex-col text-xs font-medium">
                                    <span class="font-mono text-slate-800 dark:text-slate-200">CI: {{ $usuario->cedula ?: 'S/N' }}</span>
                                    <span class="text-slate-400">Cel: {{ $usuario->celular ?: 'S/N' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2 max-w-xs truncate">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span class="truncate">{{ $usuario->direccion ?: 'Sin dirección' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                    @anypermiso(['administracion.usuarios.permisos', 'administracion.usuarios.editar'])
                                    <button wire:click="openPermissionsModal({{ $usuario->id }})" 
                                            class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950 hover:text-teal-600 dark:hover:text-teal-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Gestionar Permisos del Usuario">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    </button>
                                    @endanypermiso

                                    @permiso('administracion.usuarios.editar')
                                    <button wire:click="openPasswordModal({{ $usuario->id }})" 
                                            class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-amber-50 dark:hover:bg-amber-950 hover:text-amber-600 dark:hover:text-amber-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Restablecer Contraseña">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                    </button>
                                    <button wire:click="edit({{ $usuario->id }})" 
                                            class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950 hover:text-teal-600 dark:hover:text-teal-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Editar Usuario">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    @endpermiso

                                    @permiso('administracion.usuarios.eliminar')
                                    <button type="button"
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Está seguro de eliminar este usuario?',
                                                text: 'Se eliminará la cuenta de {{ addslashes($nombreVisible ?: $usuario->name) }}. El usuario perderá el acceso al sistema.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'delete',
                                                componentId: '{{ $this->getId() }}',
                                                method: 'delete',
                                                params: [{{ $usuario->id }}]
                                            })" 
                                            class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950 hover:text-rose-600 dark:hover:text-rose-400 transition-colors focus:outline-none cursor-pointer"
                                            title="Eliminar Usuario">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No se encontraron usuarios</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">No hay coincidencias con tu búsqueda actual o aún no se han creado registros.</p>
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
        @if($usuarios->hasPages())
            <div class="pt-2">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Crear / Editar Usuario (Compacto y Centrado en pantalla) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closeModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl border border-slate-200 dark:border-slate-800 z-10 my-auto">
                <form wire:submit="save">
                    <!-- Cabecera del Modal (Rule 7) -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $usuarioId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                {{ $usuarioId ? 'Editar Usuario' : 'Nuevo Usuario' }}
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Email -->
                            <div class="sm:col-span-2">
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Correo Electrónico (Login) <span class="text-rose-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       wire:model="email" 
                                       placeholder="Ej: juan.perez@farmacia.com" 
                                       autofocus
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('email') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('email')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nombres -->
                            <div>
                                <label for="nombres" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Nombres <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nombres" 
                                       wire:model="nombres" 
                                       placeholder="Ej: Juan Carlos" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('nombres') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
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
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('ap_paterno') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
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
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('ap_materno') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('ap_materno')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cédula de Identidad -->
                            <div>
                                <label for="cedula" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Cédula / Documento <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="cedula" 
                                       wire:model="cedula" 
                                       placeholder="Ej: 8934521" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('cedula') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('cedula')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Rol del Usuario -->
                            <div>
                                <label for="rol_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Rol / Nivel de Acceso <span class="text-rose-500">*</span>
                                </label>
                                <select id="rol_id" 
                                        wire:model="rol_id" 
                                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('rol_id') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                    <option value="">Selecciona un rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('rol_id')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sucursal Asignada -->
                            <div>
                                <label for="sucursal_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Sucursal Asignada <span class="text-rose-500">*</span>
                                </label>
                                <select id="sucursal_id" 
                                        wire:model="sucursal_id" 
                                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('sucursal_id') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                    <option value="">Selecciona sucursal</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }} (Cód. {{ $sucursal->codigo_sucursal }})</option>
                                    @endforeach
                                </select>
                                @error('sucursal_id')
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
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('celular') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('celular')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dirección -->
                            <div class="sm:col-span-2">
                                <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Dirección Domiciliaria
                                </label>
                                <input type="text" 
                                       id="direccion" 
                                       wire:model="direccion" 
                                       placeholder="Ej: Zona Miraflores, Calle Cuba #123" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('direccion') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-teal-500 focus:ring-teal-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                                @error('direccion')
                                    <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-teal-500/10 border border-teal-500/20 text-teal-800 dark:text-teal-300 text-xs flex items-start gap-2.5">
                            <i class="fas fa-info-circle shrink-0 text-teal-500 mt-0.5"></i>
                            <div>
                                <p class="font-bold">Credenciales Iniciales y Punto de Venta</p>
                                <p class="mt-0.5 opacity-90">
                                    @if (!$usuarioId)
                                        La contraseña temporal del nuevo usuario será su <b>Cédula de Identidad</b>. Podrá cambiarla luego.
                                    @endif
                                    El usuario quedará vinculado con el primer <b>Punto de Venta</b> de la sucursal seleccionada para sus operaciones y facturación en el sistema.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pie del Modal (Rules 6 & 8) -->
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

    <!-- Modal para Restablecer Contraseña (Compacto y Centrado) -->
    @if ($showPasswordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-labelledby="password-modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closePasswordModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full border border-slate-200 dark:border-slate-800 z-10 my-auto">
                <form wire:submit="saveNewPassword">
                    <!-- Cabecera del Modal (Rule 7) -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-white text-sm">
                                <i class="fas fa-key"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="password-modal-title">
                                Restablecer Contraseña
                            </h3>
                        </div>
                        <button 
                            wire:click="closePasswordModal" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Cuerpo del Modal -->
                    <div class="p-6 space-y-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Usuario: <b class="text-slate-800 dark:text-slate-200">{{ $resetUsuarioNombre }}</b>
                        </p>
                        <div>
                            <label for="newPassword" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Nueva Contraseña <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   id="newPassword" 
                                   wire:model="newPassword" 
                                   placeholder="Ingresa la nueva clave de acceso..." 
                                   autofocus
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border {{ $errors->has('newPassword') ? 'border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 focus:border-amber-500 focus:ring-amber-500/20' }} rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:outline-none transition-all">
                            @error('newPassword')
                                <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5">
                            <i class="fas fa-exclamation-triangle shrink-0 text-amber-500 mt-0.5"></i>
                            <div>
                                <p class="font-bold">Información de Seguridad</p>
                                <p class="mt-0.5 opacity-90">La nueva contraseña reemplazará inmediatamente la clave actual.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pie del Modal (Rules 6 & 8) -->
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" 
                                wire:click="closePasswordModal" 
                                class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                            Cancelar
                        </button>

                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="saveNewPassword"
                                class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs sm:text-sm shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <span wire:loading.remove wire:target="saveNewPassword" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-key"></i>
                                <span>Restablecer</span>
                            </span>
                            <span wire:loading wire:target="saveNewPassword" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Procesando...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal para Gestionar Permisos del Usuario (Rule 7 & Rule 8) -->
    @if ($showPermissionsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-labelledby="permissions-modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop oscuro -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                 wire:click="closePermissionsModal"></div>

            <!-- Contenido del Modal -->
            <div class="relative bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-4xl w-full border border-slate-200 dark:border-slate-800 z-10 my-auto flex flex-col max-h-[92vh]">
                
                <!-- Cabecera del Modal (Rule 7) -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white text-base shadow-sm">
                            <i class="fas fa-user-shield"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="permissions-modal-title">
                                    Permisos del Colaborador
                                </h3>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $permisosUsuarioRol }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Gestionando accesos específicos para <strong class="text-slate-800 dark:text-slate-200">{{ $permisosUsuarioNombre }}</strong>
                            </p>
                        </div>
                    </div>
                    <button 
                        wire:click="closePermissionsModal" 
                        type="button" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1.5 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                    >
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                @if ($permisosUsuarioEsAdmin)
                    <div class="mx-6 mt-4 p-3 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/80 text-teal-800 dark:text-teal-300 text-xs flex items-start gap-2.5 shrink-0">
                        <i class="fas fa-crown shrink-0 text-teal-600 dark:text-teal-400 mt-0.5 text-sm"></i>
                        <div>
                            <p class="font-bold">Colaborador con Rol Administrador</p>
                            <p class="mt-0.5 opacity-90">Este usuario cuenta con bypass administrativo y tiene acceso irrestricto a todas las funciones del sistema por defecto. Los permisos seleccionados aquí quedarán asignados formalmente en base de datos.</p>
                        </div>
                    </div>
                @endif

                <!-- Barra de Herramientas y Filtros del Modal -->
                <div class="px-6 py-3 bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                wire:click="seleccionarTodosPermisos"
                                class="px-3 py-1.5 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 hover:bg-teal-100 dark:hover:bg-teal-900/60 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1.5">
                            <i class="fas fa-check-double text-[10px]"></i>
                            <span>Marcar Todos</span>
                        </button>
                        <button type="button" 
                                wire:click="desmarcarTodosPermisos"
                                class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1.5">
                            <i class="fas fa-times text-[10px]"></i>
                            <span>Desmarcar Todos</span>
                        </button>
                    </div>

                    <!-- Buscador interno de permisos -->
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="permisosSearch" 
                               placeholder="Filtrar permisos o módulos..." 
                               class="w-full pl-9 pr-8 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs" />
                        @if ($permisosSearch !== '')
                            <button wire:click="$set('permisosSearch', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Cuerpo Desplazable del Modal -->
                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    @forelse ($permisosAgrupados as $moduloNombre => $apartados)
                        <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden bg-slate-50/30 dark:bg-slate-900/40">
                            <!-- Cabecera de Módulo con botón de alternancia rápida -->
                            <div class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-teal-600 text-white flex items-center justify-center text-xs">
                                        <i class="fas fa-layer-group"></i>
                                    </span>
                                    <span class="font-bold text-xs uppercase tracking-wider text-slate-800 dark:text-slate-100">
                                        {{ $moduloNombre }}
                                    </span>
                                </div>
                                <button type="button" 
                                        wire:click="toggleModulo('{{ addslashes($moduloNombre) }}')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-semibold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 hover:bg-teal-100 dark:hover:bg-teal-900/60 border border-teal-200 dark:border-teal-800 transition-colors cursor-pointer">
                                    Alternar Módulo
                                </button>
                            </div>

                            <!-- Listado de Apartados y Permisos -->
                            <div class="p-4 space-y-4">
                                @foreach ($apartados as $apartadoNombre => $items)
                                    <div>
                                        <h4 class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                            <i class="fas fa-angle-right text-teal-500 text-[10px]"></i>
                                            <span>{{ $apartadoNombre }}</span>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                            @foreach ($items as $permiso)
                                                @php
                                                    $isChecked = in_array($permiso->id, $selectedPermisos);
                                                @endphp
                                                <label class="flex items-start gap-3 p-3 rounded-xl border transition-all duration-150 cursor-pointer {{ $isChecked ? 'bg-teal-50/30 dark:bg-teal-950/20 border-teal-500/50 ring-1 ring-teal-500/30 shadow-2xs' : 'bg-white dark:bg-slate-800/70 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                                    <input type="checkbox" 
                                                           wire:model="selectedPermisos" 
                                                           value="{{ $permiso->id }}"
                                                           class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-teal-600 focus:ring-teal-500 cursor-pointer">
                                                    <div class="flex flex-col select-none min-w-0">
                                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-100 leading-tight">
                                                            {{ $permiso->descripcion ?: $permiso->nombre }}
                                                        </span>
                                                        <span class="text-[10px] font-mono text-slate-400 dark:text-slate-400 mt-0.5 truncate">
                                                            {{ $permiso->nombre }}
                                                        </span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-12 px-4 text-center">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                <i class="fas fa-search text-lg"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No se encontraron permisos</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Ningún permiso coincide con el filtro de búsqueda "{{ $permisosSearch }}".</p>
                            <button type="button" wire:click="$set('permisosSearch', '')" class="mt-3 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                                Limpiar filtro
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Pie del Modal (Rules 6 & 8) -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                            {{ count($selectedPermisos) }} de {{ $totalPermisosSistema }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline">permisos activos asignados</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" 
                                wire:click="closePermissionsModal" 
                                class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                            Cancelar
                        </button>

                        <button type="button" 
                                wire:click="savePermissions"
                                wire:loading.attr="disabled"
                                wire:target="savePermissions"
                                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs sm:text-sm shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <span wire:loading.remove wire:target="savePermissions" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-save"></i>
                                <span>Guardar Permisos</span>
                            </span>
                            <span wire:loading wire:target="savePermissions" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Guardando...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
