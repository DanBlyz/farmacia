<x-app-layout>
    <x-slot name="header">
        {{ __('Panel de Control General') }}
    </x-slot>

    <div class="space-y-6">
        <!-- Banner de Bienvenida -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-teal-900 via-emerald-900 to-slate-900 text-white p-6 sm:p-8 shadow-xl">
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-500/30 text-teal-300 text-xs font-semibold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Estación Operativa Activa
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Hola, {{ Auth::user()->name }} 👋
                    </h1>
                    <p class="text-slate-300 text-sm mt-1 max-w-xl">
                        Bienvenido a tu panel general de control <strong class="text-white font-semibold">Farma ERP</strong>. Aquí tienes un resumen en tiempo real de inventarios, dispensación y facturación.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="#" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-teal-500/30 transition-transform duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        <span>Abrir POS</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Fila de Tarjetas de Resumen (AdminLTE Info Boxes) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Tarjeta 1: Ventas del Día -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ventas de Hoy</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1">Bs. 14,850</p>
                        <div class="flex items-center gap-1 mt-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            <span>+18.4% vs ayer</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Productos en Stock -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Catálogo Almacén</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1">2,480</p>
                        <div class="flex items-center gap-1 mt-2 text-xs font-semibold text-teal-600 dark:text-teal-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>12 bajos en stock</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Lotes por Vencer -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Lotes por Vencer</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-amber-500 mt-1">8 Lotes</p>
                        <div class="flex items-center gap-1 mt-2 text-xs font-semibold text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>Próximos 30 días</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 4: Clientes & Recetas -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Clientes Atendidos</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1">142</p>
                        <div class="flex items-center gap-1 mt-2 text-xs font-semibold text-sky-600 dark:text-sky-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span>Hoy en 3 turnos</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas (AdminLTE Quick Action Panel) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Acciones Rápidas del Sistema
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="#" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-teal-50 dark:hover:bg-teal-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-teal-300 dark:hover:border-teal-800 text-slate-700 dark:text-slate-300 hover:text-teal-700 dark:hover:text-teal-300 transition-all group font-semibold text-xs">
                    <span class="p-2 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 group-hover:scale-110 transition-transform">🛒</span>
                    <span>Punto de Venta POS</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-emerald-300 dark:hover:border-emerald-800 text-slate-700 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-300 transition-all group font-semibold text-xs">
                    <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">📦</span>
                    <span>Ingresar Lote / Producto</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-sky-50 dark:hover:bg-sky-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-sky-300 dark:hover:border-sky-800 text-slate-700 dark:text-slate-300 hover:text-sky-700 dark:hover:text-sky-300 transition-all group font-semibold text-xs">
                    <span class="p-2 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 group-hover:scale-110 transition-transform">📋</span>
                    <span>Consultar Kardex</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-slate-200/60 dark:border-slate-700/60 hover:border-amber-300 dark:hover:border-amber-800 text-slate-700 dark:text-slate-300 hover:text-amber-700 dark:hover:text-amber-300 transition-all group font-semibold text-xs">
                    <span class="p-2 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">📈</span>
                    <span>Cierre Diario SIAT</span>
                </a>
            </div>
        </div>

        <!-- Dos Columnas: Últimas Ventas / Estado Operativo -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tabla de Movimientos Recientes (2 Columnas) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span>🧾 Últimos Movimientos de Ventas</span>
                    </h3>
                    <a href="#" class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">Ver todo &rarr;</a>
                </div>
                <div class="overflow-x-auto mt-4 flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                <th class="pb-3">Factura / Ticket</th>
                                <th class="pb-3">Detalle / Medicamento</th>
                                <th class="pb-3">Monto</th>
                                <th class="pb-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <tr>
                                <td class="py-3 font-mono font-semibold text-slate-800 dark:text-slate-200">#FAC-00892</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">Paracetamol 500mg + Ibuprofeno (2 cj)</td>
                                <td class="py-3 font-bold text-slate-800 dark:text-slate-200">Bs. 45.00</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 text-[10px]">Completado</span></td>
                            </tr>
                            <tr>
                                <td class="py-3 font-mono font-semibold text-slate-800 dark:text-slate-200">#FAC-00891</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">Amoxicilina 500mg Jarabe Ped.</td>
                                <td class="py-3 font-bold text-slate-800 dark:text-slate-200">Bs. 62.50</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 text-[10px]">Completado</span></td>
                            </tr>
                            <tr>
                                <td class="py-3 font-mono font-semibold text-slate-800 dark:text-slate-200">#FAC-00890</td>
                                <td class="py-3 text-slate-600 dark:text-slate-400">Losartán 50mg + Omeprazol 20mg</td>
                                <td class="py-3 font-bold text-slate-800 dark:text-slate-200">Bs. 110.00</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 text-[10px]">Completado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel de Estado Operativo & Conectividad -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider pb-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <span>⚙️ Estado del Sistema</span>
                    </h3>
                    <div class="space-y-4 mt-4 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Base de Datos MySQL</span>
                            <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Conectado
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Facturación SIAT</span>
                            <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Token OK
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Motor Livewire</span>
                            <span class="font-mono font-bold text-teal-600 dark:text-teal-400">v4.x Reactivo</span>
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Estructura base lista para integrar módulos de Inventario, POS y Kardex.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
