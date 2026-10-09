<!-- Header / Topbar AdminLTE Style -->
<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 transition-colors duration-200 shadow-sm select-none">
    
    <!-- Lado Izquierdo: Toggle Sidebar & Breadcrumbs -->
    <div class="flex items-center gap-4">
        <!-- Botón Toggle de Sidebar (Escritorio & Móvil) -->
        <button @click="if (window.innerWidth < 1024) { mobileOpen = !mobileOpen } else { sidebarOpen = !sidebarOpen }" 
                class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none"
                title="Contraer/Expandir Menú">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
        </button>

        <!-- Breadcrumbs / Título -->
        <div class="hidden sm:flex items-center gap-2 text-sm">
            <span class="text-slate-400 dark:text-slate-500 font-medium">Farma ERP</span>
            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="font-bold text-slate-800 dark:text-slate-100">
                {{ $header ?? __('Estación de Trabajo') }}
            </span>
        </div>
    </div>

    <!-- Buscador Central Rápido -->
    {{-- <div class="hidden md:flex flex-1 max-w-md mx-6">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <input type="text" 
                   placeholder="Buscar medicamento (Nombre, Código, Lote) o Cliente CI/NIT..." 
                   class="w-full pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800/80 border border-transparent dark:border-slate-700/60 rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none transition-all">
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <kbd class="hidden lg:inline-block px-1.5 py-0.5 text-[10px] font-mono font-semibold text-slate-400 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded">Ctrl+K</kbd>
            </span>
        </div>
    </div> --}}

    <!-- Lado Derecho: Acciones, Modo Oscuro y Perfil -->
    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Botón de Venta Rápida (POS) -->
        <a href="#" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition duration-150 transform hover:-translate-y-0.5 active:translate-y-0">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
            <span>Nueva Venta POS</span>
        </a>

        <!-- Botón Modo Oscuro / Claro -->
        <button @click="darkMode = !darkMode; if (darkMode) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark') } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light') }" 
                class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors focus:outline-none"
                title="Alternar Modo Oscuro/Claro">
            <!-- Icono Luna (para modo claro) -->
            <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            <!-- Icono Sol (para modo oscuro) -->
            <svg x-show="darkMode" x-cloak class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        </button>

        <!-- Botón Alertas de Stock / Notificaciones -->
        <div class="relative" x-data="{ openNotif: false }">
            <button @click="openNotif = !openNotif" @click.outside="openNotif = false" 
                    class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors relative focus:outline-none"
                    title="Alertas y Notificaciones">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-900 animate-pulse"></span>
            </button>

            <!-- Dropdown Alertas -->
            <div x-show="openNotif" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 x-cloak
                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-2 z-50">
                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="font-bold text-xs uppercase text-slate-700 dark:text-slate-300">Alertas de Almacén</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">2 Nuevas</span>
                </div>
                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    <a href="#" class="block p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-start gap-2.5">
                            <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0">⚠️</span>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Lote próximo a vencer</p>
                                <p class="text-slate-500 dark:text-slate-400 mt-0.5">Paracetamol 500mg - Lote L-2024 vencerá en 15 días.</p>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="block p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-start gap-2.5">
                            <span class="p-1.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 shrink-0">🚨</span>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Stock Mínimo Alcanzado</p>
                                <p class="text-slate-500 dark:text-slate-400 mt-0.5">Amoxicilina 500mg (Quedan 4 unidades en almacén).</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Divisor -->
        <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

        <!-- Menú de Usuario -->
        <div class="relative" x-data="{ openUser: false }">
            <button @click="openUser = !openUser" @click.outside="openUser = false" 
                    class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-600 to-emerald-700 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="hidden lg:block text-left">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight truncate max-w-[120px]">{{ Auth::user()->name ?? 'Administrador' }}</p>
                    <p class="text-[10px] font-medium text-teal-600 dark:text-teal-400 leading-tight">Administrador ERP</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 shrink-0 hidden lg:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>

            <!-- Dropdown de Usuario -->
            <div x-show="openUser" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 x-cloak
                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-2 z-50 text-xs">
                
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                    <p class="font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</p>
                    <p class="text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>

                <div class="py-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <span>Mi Perfil de Personal</span>
                    </a>
                    <a href="#" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        <span>Cambiar Sucursal Activa</span>
                    </a>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-1 mt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 font-semibold transition-colors text-left">
                            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
