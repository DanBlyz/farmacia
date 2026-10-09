@php
    // Helper para verificar si alguna ruta del grupo está activa y abrir el acordeón
    $isGroupActive = function ($routes) {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return true;
            }
        }
        return false;
    };
@endphp

<!-- Sidebar AdminLTE Style -->
<aside :class="{
           'translate-x-0 w-64': sidebarOpen && !mobileOpen,
           '-translate-x-full lg:translate-x-0 lg:w-20': !sidebarOpen && !mobileOpen,
           'translate-x-0 w-64 z-50': mobileOpen
       }" 
       class="fixed inset-y-0 left-0 bg-slate-900 text-slate-300 transition-all duration-300 ease-in-out flex flex-col z-40 border-r border-slate-800 shadow-2xl lg:shadow-none select-none">
    
    <!-- Brand Logo / Cabecera -->
    <div class="h-16 flex items-center justify-between px-4 bg-slate-950/60 border-b border-slate-800 shrink-0 overflow-hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-bold text-white tracking-wide group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-400 flex items-center justify-center text-white shadow-lg shadow-teal-500/20 shrink-0 group-hover:scale-105 transition-transform duration-200">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <div :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="transition-opacity duration-200 whitespace-nowrap">
                <span class="text-lg tracking-tight font-extrabold text-white">FARMA</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 ml-1 border border-teal-500/30">v13</span>
            </div>
        </a>

        <!-- Botón cerrar en móvil -->
        <button @click="mobileOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <!-- Panel de Usuarior y Sucursal -->
    <div :class="{'px-4 py-3.5': sidebarOpen || mobileOpen, 'p-2 justify-center': !sidebarOpen && !mobileOpen}" 
         class="border-b border-slate-800/80 bg-slate-900/50 shrink-0 flex items-center gap-3 overflow-hidden">
        <div class="relative shrink-0">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-600 to-slate-700 flex items-center justify-center text-white font-bold text-sm ring-2 ring-slate-800">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
            </div>
            <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-slate-900" title="En Línea"></span>
        </div>
        <div :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="overflow-hidden whitespace-nowrap">
            <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Administrador' }}</p>
            <div class="flex items-center gap-1.5 text-xs text-teal-400 font-medium">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                <span class="truncate">Sucursal Principal</span>
            </div>
        </div>
    </div>

    <!-- Buscador del Menú (AdminLTE Style) -->
    {{-- <div :class="{'px-4 py-3': sidebarOpen || mobileOpen, 'px-2 py-3': !sidebarOpen && !mobileOpen}" class="border-b border-slate-800 shrink-0">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <input type="text" 
                   placeholder="Buscar menú..." 
                   :class="{'pl-9 pr-3 py-1.5 w-full': sidebarOpen || mobileOpen, 'pl-8 pr-1 py-1.5 w-full text-center': !sidebarOpen && !mobileOpen}"
                   class="bg-slate-950/80 border border-slate-800 rounded-lg text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
        </div>
    </div> --}}

    <!-- Contenedor del Menú con Scroll -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 space-y-6 custom-scrollbar">
        
        <!-- SECCIÓN: PRINCIPAL -->
        <div class="space-y-1">
            <p :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Principal
            </p>
            
            <a href="{{ route('dashboard') }}" 
               :class="{'px-3': sidebarOpen || mobileOpen, 'justify-center px-0': !sidebarOpen && !mobileOpen}"
               class="flex items-center gap-3 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-lg shadow-teal-500/20 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
               title="Dashboard">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="truncate">Panel de Control</span>
            </a>
        </div>

        <!-- SECCIÓN: ADMINISTRACIÓN -->
        <div class="space-y-1" x-data="{ open: {{ request()->routeIs('administracion.*') ? 'true' : 'false' }} }">
            <p :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Sistema
            </p>
            
            <button @click="open = !open" 
                    :class="{'px-3': sidebarOpen || mobileOpen, 'justify-center px-0': !sidebarOpen && !mobileOpen}"
                    class="w-full flex items-center justify-between py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150"
                    title="Configuración">
                <div class="flex items-center gap-3 truncate">
                    <svg class="w-5 h-5 text-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="truncate">Administración</span>
                </div>
                <svg :class="{'hidden': !sidebarOpen && !mobileOpen, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div x-show="open" x-collapse x-cloak :class="{'hidden': !sidebarOpen && !mobileOpen}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                <a href="{{ route('administracion.roles') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.roles') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Roles
                </a>
                <a href="{{ route('administracion.sucursales') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.sucursales') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Sucursales
                </a>
                <a href="{{ route('administracion.usuarios') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.usuarios') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Usuarios
                </a>
                <a href="{{ route('administracion.proveedores') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.proveedores') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Proveedores
                </a>
                <a href="{{ route('administracion.clientes') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.clientes') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Clientes
                </a>
            </div>
        </div>

        <!-- SECCIÓN: SINCRONIZACION SIAT -->
        <div class="space-y-1" x-data="{ open: {{ request()->routeIs('sincronizacion.*') ? 'true' : 'false' }} }">
            <p :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                SIAT
            </p>
            
            <button @click="open = !open" 
                    :class="{'px-3': sidebarOpen || mobileOpen, 'justify-center px-0': !sidebarOpen && !mobileOpen}"
                    class="w-full flex items-center justify-between py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150"
                    title="Configuración">
                <div class="flex items-center gap-3 truncate">
                    <svg class="w-5 h-5 text-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="truncate">Sincronizacion</span>
                </div>
                <svg :class="{'hidden': !sidebarOpen && !mobileOpen, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div x-show="open" x-collapse x-cloak :class="{'hidden': !sidebarOpen && !mobileOpen}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                <a href="{{ route('sincronizacion.catalogos') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('sincronizacion.catalogos') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Catalogos
                </a>
                <a href="{{ route('sincronizacion.eventos') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('sincronizacion.eventos') ? 'text-purple-400 font-bold bg-purple-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Eventos Significativos
                </a>
            </div>
        </div>

        <!-- SECCIÓN: INVENTARIO & KARDEX -->
        <div class="space-y-1" x-data="{ open: false }">
            <p :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Almacén & Inventario
            </p>
            
            <!-- Grupo Desplegable -->
            <button @click="open = !open" 
                    :class="{'px-3': sidebarOpen || mobileOpen, 'justify-center px-0': !sidebarOpen && !mobileOpen}"
                    class="w-full flex items-center justify-between py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150"
                    title="Inventario">
                <div class="flex items-center gap-3 truncate">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <span :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="truncate">Inventario & Productos</span>
                </div>
                <svg :class="{'hidden': !sidebarOpen && !mobileOpen, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <!-- Submenú -->
            <div x-show="open" x-collapse x-cloak :class="{'hidden': !sidebarOpen && !mobileOpen}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Catálogo de Medicamentos
                </a>
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Lotes & Vencimientos
                </a>
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Kardex de Movimientos
                </a>
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Categorías y Laboratorios
                </a>
            </div>
        </div>

        <!-- SECCIÓN: VENTAS & POS -->
        <div class="space-y-1" x-data="{ open: false }">
            <p :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Ventas & Caja
            </p>
            
            <button @click="open = !open" 
                    :class="{'px-3': sidebarOpen || mobileOpen, 'justify-center px-0': !sidebarOpen && !mobileOpen}"
                    class="w-full flex items-center justify-between py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150"
                    title="Ventas">
                <div class="flex items-center gap-3 truncate">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="truncate">Punto de Venta (POS)</span>
                </div>
                <svg :class="{'hidden': !sidebarOpen && !mobileOpen, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div x-show="open" x-collapse x-cloak :class="{'hidden': !sidebarOpen && !mobileOpen}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                <a href="#" class="block py-2 px-3 rounded-lg text-emerald-400 font-semibold hover:bg-emerald-500/10 transition-colors truncate">
                    Terminal POSr
                </a>
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Facturación SIAT en Línea
                </a>
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Apertura y Cierre de Caja
                </a>
                <a href="#" class="block py-2 px-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors truncate">
                    Historial de Ventas
                </a>
            </div>
        </div>

        <!-- SECCIÓN: REPORTES -->
        <div class="space-y-1">
            <p :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Inteligencia
            </p>
            
            <a href="#" 
               :class="{'px-3': sidebarOpen || mobileOpen, 'justify-center px-0': !sidebarOpen && !mobileOpen}"
               class="flex items-center gap-3 py-2.5 rounded-xl font-medium text-sm text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150"
               title="Reportes">
                <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                <span :class="{'hidden': !sidebarOpen && !mobileOpen, 'block': sidebarOpen || mobileOpen}" class="truncate">Reportes & Kardex PDF</span>
            </a>
        </div>

    </div>

    <!-- Footer del Sidebar (Información de Estado) -->
    <div :class="{'px-4 py-3': sidebarOpen || mobileOpen, 'p-2 justify-center': !sidebarOpen && !mobileOpen}" 
         class="border-t border-slate-800/80 bg-slate-950/60 shrink-0 flex items-center justify-between text-xs text-slate-500">
        <div :class="{'hidden': !sidebarOpen && !mobileOpen, 'flex': sidebarOpen || mobileOpen}" class="items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span class="font-medium text-slate-400">SIAT Activo</span>
        </div>
        <span class="font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300">PHP 8.3</span>
    </div>
</aside>
