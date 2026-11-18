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
<aside @mouseenter="if (!sidebarOpen && window.innerWidth >= 1024) sidebarHovered = true"
       @mouseleave="if (!sidebarOpen && window.innerWidth >= 1024) sidebarHovered = false"
       :class="{
           'translate-x-0 w-64 lg:w-64': isExpanded && !mobileOpen,
           '-translate-x-full lg:translate-x-0 lg:w-20': !isExpanded && !mobileOpen,
           'translate-x-0 w-64 z-50': mobileOpen,
           'shadow-2xl z-50': !sidebarOpen && sidebarHovered
       }" 
       class="fixed inset-y-0 left-0 bg-slate-900 text-slate-300 transition-all duration-300 ease-in-out flex flex-col z-40 border-r border-slate-800 shadow-2xl lg:shadow-none select-none">
    
    <!-- Brand Logo / Cabecera -->
    <div :class="{'px-4 justify-between': isExpanded, 'px-0 justify-center': !isExpanded}"
         class="h-16 flex items-center bg-slate-950/60 border-b border-slate-800 shrink-0 overflow-hidden transition-all duration-300">
        <a href="{{ route('dashboard') }}" 
           :class="{'justify-start': isExpanded, 'justify-center w-full': !isExpanded}"
           class="flex items-center gap-3 font-bold text-white tracking-wide group">
            <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-md shrink-0 group-hover:scale-105 transition-transform duration-200">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <div :class="{'hidden': !isExpanded, 'block': isExpanded}" class="transition-opacity duration-200 whitespace-nowrap">
                <span class="text-lg tracking-tight font-extrabold text-white">KERIGMA</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 ml-1 border border-teal-500/30">v1</span>
            </div>
        </a>

        <!-- Botón cerrar en móvil -->
        <button @click="mobileOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <!-- Contenedor del Menú con Scroll -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 space-y-6 custom-scrollbar">
        
        <!-- SECCIÓN: PRINCIPAL -->
        <div class="space-y-1">
            <p :class="{'hidden': !isExpanded, 'block': isExpanded}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Principal
            </p>
            
            <a href="{{ route('dashboard') }}" 
               :class="{'px-3': isExpanded, 'justify-center px-0': !isExpanded}"
               class="flex items-center gap-3 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-teal-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
               title="Dashboard">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span :class="{'hidden': !isExpanded, 'block': isExpanded}" class="truncate">Panel de Control</span>
            </a>
        </div>

        <!-- SECCIÓN: ADMINISTRACIÓN -->
        @anypermiso(['administracion.roles.ver', 'administracion.sucursales.ver', 'administracion.usuarios.ver', 'administracion.proveedores.ver', 'administracion.clientes.ver'])
        <div class="space-y-1" x-data="{ open: {{ request()->routeIs('administracion.*') ? 'true' : 'false' }} }">
            <p :class="{'hidden': !isExpanded, 'block': isExpanded}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Sistema
            </p>
            
            <button @click="open = !open" 
                    :class="{'px-3 justify-between': isExpanded, 'justify-center px-0': !isExpanded}"
                    class="w-full flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 cursor-pointer"
                    title="Configuración">
                <div class="flex items-center" :class="{'gap-3 truncate': isExpanded, 'justify-center': !isExpanded}">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span :class="{'hidden': !isExpanded, 'block': isExpanded}" class="truncate">Administración</span>
                </div>
                <svg :class="{'hidden': !isExpanded, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div x-show="open" x-collapse x-cloak :class="{'hidden': !isExpanded}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                @permiso('administracion.roles.ver')
                <a href="{{ route('administracion.roles') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.roles') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Roles
                </a>
                @endpermiso

                @permiso('administracion.sucursales.ver')
                <a href="{{ route('administracion.sucursales') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.sucursales') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Sucursales
                </a>
                @endpermiso

                @permiso('administracion.usuarios.ver')
                <a href="{{ route('administracion.usuarios') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.usuarios') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Usuarios
                </a>
                @endpermiso

                @permiso('administracion.proveedores.ver')
                <a href="{{ route('administracion.proveedores') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.proveedores') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Proveedores
                </a>
                @endpermiso

                @permiso('administracion.clientes.ver')
                <a href="{{ route('administracion.clientes') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('administracion.clientes') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Clientes
                </a>
                @endpermiso
            </div>
        </div>
        @endanypermiso

        <!-- SECCIÓN: SINCRONIZACION SIAT -->
        @anypermiso(['sincronizacion.catalogos.ver', 'sincronizacion.eventos.ver'])
        <div class="space-y-1" x-data="{ open: {{ request()->routeIs('sincronizacion.*') ? 'true' : 'false' }} }">
            <p :class="{'hidden': !isExpanded, 'block': isExpanded}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                SIAT
            </p>
            
            <button @click="open = !open" 
                    :class="{'px-3 justify-between': isExpanded, 'justify-center px-0': !isExpanded}"
                    class="w-full flex items-center py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 cursor-pointer"
                    title="Configuración">
                <div class="flex items-center" :class="{'gap-3 truncate': isExpanded, 'justify-center': !isExpanded}">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span :class="{'hidden': !isExpanded, 'block': isExpanded}" class="truncate">Sincronización</span>
                </div>
                <svg :class="{'hidden': !isExpanded, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div x-show="open" x-collapse x-cloak :class="{'hidden': !isExpanded}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                @permiso('sincronizacion.catalogos.ver')
                <a href="{{ route('sincronizacion.catalogos') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('sincronizacion.catalogos') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Catálogos
                </a>
                @endpermiso

                @permiso('sincronizacion.eventos.ver')
                <a href="{{ route('sincronizacion.eventos') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('sincronizacion.eventos') ? 'text-teal-400 font-bold bg-teal-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Eventos Significativos
                </a>
                @endpermiso
            </div>
        </div>
        @endanypermiso

        <!-- SECCIÓN: INVENTARIO & KARDEX -->
        @anypermiso(['inventario.productos.ver', 'inventario.lotes.ver', 'inventario.kardex.ver'])
        <div class="space-y-1" x-data="{ open: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }} }">
            <p :class="{'hidden': !isExpanded, 'block': isExpanded}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Almacén & Inventario
            </p>
            
            <!-- Grupo Desplegable -->
            <button @click="open = !open" 
                    :class="{'px-3 justify-between': isExpanded, 'justify-center px-0': !isExpanded}"
                    class="w-full flex items-center py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }} transition-all duration-150 cursor-pointer"
                    title="Inventario">
                <div class="flex items-center" :class="{'gap-3 truncate': isExpanded, 'justify-center': !isExpanded}">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <span :class="{'hidden': !isExpanded, 'block': isExpanded}" class="truncate">Inventario & Productos</span>
                </div>
                <svg :class="{'hidden': !isExpanded, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <!-- Submenú -->
            <div x-show="open" x-collapse x-cloak :class="{'hidden': !isExpanded}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                @permiso('inventario.productos.ver')
                <a href="{{ route('inventario.productos') }}" wire:navigate class="block py-2 px-3 rounded-lg {{ request()->routeIs('inventario.productos') ? 'bg-teal-600 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Catálogo de Medicamentos
                </a>
                @endpermiso
                @permiso('inventario.lotes.ver')
                <a href="{{ route('inventario.lotes') }}" wire:navigate class="block py-2 px-3 rounded-lg {{ request()->routeIs('inventario.lotes') ? 'bg-teal-600 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Lotes & Vencimientos
                </a>
                @endpermiso
                @permiso('inventario.kardex.ver')
                <a href="{{ route('inventario.kardex') }}" wire:navigate class="block py-2 px-3 rounded-lg {{ request()->routeIs('inventario.kardex') ? 'bg-teal-600 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Kardex de Movimientos
                </a>
                @endpermiso
            </div>
        </div>
        @endanypermiso

        <!-- SECCIÓN: VENTAS & POS -->
        @anypermiso(['ventas.pos.ver', 'cajas.control.ver', 'ventas.facturas.ver'])
        <div class="space-y-1" x-data="{ open: {{ request()->routeIs('ventas.*', 'cajas.*') ? 'true' : 'false' }} }">
            <p :class="{'hidden': !isExpanded, 'block': isExpanded}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Ventas & Caja
            </p>
            
            <button @click="open = !open" 
                    :class="{'px-3 justify-between': isExpanded, 'justify-center px-0': !isExpanded}"
                    class="w-full flex items-center py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('ventas.*', 'cajas.*') ? 'text-white bg-slate-800/80 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }} transition-all duration-150 cursor-pointer"
                    title="Ventas">
                <div class="flex items-center" :class="{'gap-3 truncate': isExpanded, 'justify-center': !isExpanded}">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span :class="{'hidden': !isExpanded, 'block': isExpanded}" class="truncate">Punto de Venta (POS)</span>
                </div>
                <svg :class="{'hidden': !isExpanded, 'rotate-90': open}" class="w-4 h-4 transition-transform duration-200 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div x-show="open" x-collapse x-cloak :class="{'hidden': !isExpanded}" class="pl-9 pr-2 py-1 space-y-1 border-l border-slate-800 ml-5 text-xs">
                @permiso('ventas.pos.ver')
                <a href="{{ route('ventas.pos') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('ventas.pos') ? 'text-emerald-400 font-semibold bg-emerald-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Facturación SIAT en Línea
                </a>
                @endpermiso

                @permiso('cajas.control.ver')
                <a href="{{ route('cajas.control') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('cajas.control') ? 'text-emerald-400 font-semibold bg-emerald-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Apertura y Cierre de Caja
                </a>
                @endpermiso

                @permiso('ventas.facturas.ver')
                <a href="{{ route('ventas.historial') }}" class="block py-2 px-3 rounded-lg {{ request()->routeIs('ventas.historial') ? 'text-emerald-400 font-semibold bg-emerald-500/10' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-colors truncate">
                    Historial de Ventas
                </a>
                @endpermiso
            </div>
        </div>
        @endanypermiso

        <!-- SECCIÓN: REPORTES -->
        @anypermiso(['reportes.inventario.ver', 'reportes.utilidades.ver', 'reportes.cajas.ver', 'reportes.clientes.ver'])
        <div class="space-y-1">
            <p :class="{'hidden': !isExpanded, 'block': isExpanded}" 
               class="px-3 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Inteligencia
            </p>
            
            <a href="{{ route('reportes.index') }}" 
               :class="{'px-3': isExpanded, 'justify-center px-0': !isExpanded}"
               class="flex items-center gap-3 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 {{ request()->routeIs('reportes.*') ? 'bg-teal-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
               title="Reportes Generales">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('reportes.*') ? 'text-white' : 'text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span :class="{'hidden': !isExpanded, 'block': isExpanded}" class="truncate">Reportes Generales</span>
            </a>
        </div>
        @endanypermiso

    </div>

    <!-- Footer del Sidebar (Información de Estado) -->
    <div :class="{'px-4 py-3': isExpanded, 'p-2 justify-center': !isExpanded}" 
         class="border-t border-slate-800/80 bg-slate-950/60 shrink-0 flex items-center justify-between text-xs text-slate-500">
    </div>
</aside>
