<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Farma ERP') }} - Sistema Integral de Gestión</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles & Scripts (Sin Vite) -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/sweetalert2/sweetalert2.all.min.js') }}"></script>

        <!-- Estilos para scrollbar personalizado y x-cloak -->
        <style>
            [x-cloak] { display: none !important; }
            .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.2); border-radius: 9999px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.4); }
        </style>
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
        @livewireStyles
        @stack('styles')
    </head>
    <body x-data="{ 
              sidebarOpen: true, 
              mobileOpen: false, 
              darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
          }" 
          x-init="if (darkMode) { document.documentElement.classList.add('dark') } else { document.documentElement.classList.remove('dark') }"
          class="font-sans antialiased bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen selection:bg-teal-500 selection:text-white">
        
        <div class="min-h-screen flex">
            <!-- Sidebar (AdminLTE Left Panel) -->
            @include('layouts.sidebar')

            <!-- Overlay para móvil -->
            <div x-show="mobileOpen" 
                 @click="mobileOpen = false" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak
                 class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-30 lg:hidden"></div>

            <!-- Wrapper del Contenido Principal (AdminLTE Main Content Wrapper) -->
            <div :class="{'lg:pl-64': sidebarOpen, 'lg:pl-20': !sidebarOpen}" 
                 class="flex-1 flex flex-col min-h-screen transition-all duration-300 w-full overflow-x-hidden">
                
                <!-- Topbar (AdminLTE Header) -->
                @include('layouts.header')

                <!-- Contenido de Página -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>

                <!-- Footer (AdminLTE Footer) -->
                @include('layouts.footer')
            </div>
        </div>
        @livewireScripts

        <!-- Scripts globales de ayuda y confirmación (SweetAlert2) -->
        <script>
            window.showConfirmSwal = function(title, html, onConfirm) {
                Swal.fire({
                    title: title,
                    html: html,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg> Sí, continuar y eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans p-6',
                        confirmButton: 'px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-md shadow-rose-500/20 ml-2 transition-all',
                        cancelButton: 'px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white transition-all'
                    }
                }).then((result) => {
                    if (result.isConfirmed && typeof onConfirm === 'function') {
                        onConfirm();
                    }
                });
            };

            window.showToastSwal = function(title, text, icon = 'success') {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    timer: 2500,
                    showConfirmButton: false,
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans'
                    }
                });
            };
        </script>
        @stack('scripts')
    </body>
</html>
