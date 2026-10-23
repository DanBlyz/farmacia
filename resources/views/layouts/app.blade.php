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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        <script data-navigate-once>
            (function() {
                if (window.__globalSwalHandlersRegistered) {
                    return;
                }
                window.__globalSwalHandlersRegistered = true;

                function dispararAlertaSwal(rawPayload) {
                    let data = rawPayload;
                    if (Array.isArray(data)) {
                        data = data[0];
                    }
                    if (!data) return;

                    if (typeof data === 'string') {
                        data = { message: data };
                    }

                    const icon = data.icon || 'info';
                    const title = data.title || (icon === 'error' ? 'Error' : (icon === 'warning' ? 'Atención' : 'Notificación'));
                    
                    let rawMsg = data.message !== undefined ? data.message : (data.text !== undefined ? data.text : (data.mensaje !== undefined ? data.mensaje : (data.msg !== undefined ? data.msg : (data.error !== undefined ? data.error : ''))));

                    let message = '';
                    if (Array.isArray(rawMsg)) {
                        message = rawMsg.map(item => {
                            if (typeof item === 'object' && item !== null) {
                                return item.descripcion || item.mensaje || JSON.stringify(item);
                            }
                            return String(item);
                        }).join('<br>');
                    } else if (typeof rawMsg === 'object' && rawMsg !== null) {
                        message = rawMsg.descripcion || rawMsg.mensaje || rawMsg.msg || rawMsg.error || JSON.stringify(rawMsg);
                    } else {
                        message = String(rawMsg || '');
                    }

                    const trimmed = message.trim();
                    if (trimmed === '[]' || trimmed === '{}' || trimmed === '' || trimmed === 'null') {
                        if (icon === 'error') {
                            message = 'Ocurrió un error en el servidor o comunicación con el SIAT. Por favor revise el Historial de Ventas o los registros del sistema.';
                        } else if (icon === 'warning') {
                            message = 'La operación se registró con advertencias del sistema.';
                        } else {
                            message = title;
                        }
                    } else {
                        message = message.replace(/\n/g, '<br>');
                    }

                    const esToast = data.toast === true;

                    const isDark = document.documentElement.classList.contains('dark');
                    const bgColor = isDark ? '#0f172a' : '#ffffff';
                    const textColor = isDark ? '#f8fafc' : '#1e293b';

                    if (esToast && icon === 'success') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true,
                            background: bgColor,
                            color: textColor,
                            customClass: {
                                popup: 'rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl font-sans'
                            }
                        });
                        Toast.fire({
                            icon: icon,
                            title: title,
                            html: message
                        });
                    } else {
                        let confirmColor = '#0d9488';
                        if (icon === 'error') confirmColor = '#e11d48';
                        if (icon === 'warning') confirmColor = '#d97706';
                        if (icon === 'info') confirmColor = '#0284c7';

                        Swal.fire({
                            icon: icon,
                            title: title,
                            html: message,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: confirmColor,
                            background: bgColor,
                            color: textColor,
                            customClass: {
                                popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans p-6 max-w-lg',
                                confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-md transition-all'
                            }
                        });
                    }
                }

                function resolverDatosEvento(event) {
                    if (!event) return null;
                    const payload = event.detail !== undefined ? event.detail : event;
                    if (Array.isArray(payload)) {
                        return payload[0];
                    }
                    if (payload && typeof payload === 'object' && payload[0] !== undefined) {
                        return payload[0];
                    }
                    return payload;
                }

                window.addEventListener('swal', (event) => dispararAlertaSwal(resolverDatosEvento(event)));
                window.addEventListener('swal-alert', (event) => dispararAlertaSwal(resolverDatosEvento(event)));

                document.addEventListener('livewire:init', () => {
                    if (window.Livewire) {
                        Livewire.on('swal', (data) => dispararAlertaSwal(resolverDatosEvento(data)));
                        Livewire.on('swal-alert', (data) => dispararAlertaSwal(resolverDatosEvento(data)));
                    }
                });

                window.addEventListener('swal:confirm', (event) => {
                    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    if (!data) return;

                    const safeParams = Array.isArray(data.params) ? data.params : (data.params !== undefined ? [data.params] : []);

                    Swal.fire({
                        title: data.title || '¿Está seguro?',
                        text: data.text || 'Esta acción no se puede deshacer.',
                        icon: data.icon || 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: data.confirmButtonText || 'Sí, confirmar',
                        cancelButtonText: data.cancelButtonText || 'Cancelar',
                        reverseButtons: true,
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                        customClass: {
                            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans p-6',
                            confirmButton: 'px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-md shadow-rose-500/20 ml-2 transition-all',
                            cancelButton: 'px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white transition-all'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (data.componentId && data.method && window.Livewire) {
                                const component = window.Livewire.find(data.componentId);
                                if (component) {
                                    component.call(data.method, ...safeParams);
                                    return;
                                }
                            }
                            if (data.event && window.Livewire) {
                                window.Livewire.dispatch(data.event, safeParams);
                            }
                        }
                    });
                });

                window.showConfirmSwal = function(title, html, onConfirm) {
                    Swal.fire({
                        title: title,
                        html: html,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, continuar y eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                        customClass: {
                            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans p-6',
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
                            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans'
                        }
                    });
                };

                window.abrirTicketPdf = function(url) {
                    const width = 440;
                    const height = 680;
                    const left = Math.max(0, (window.screen.width - width) / 2);
                    const top = Math.max(0, (window.screen.height - height) / 2);
                    const popup = window.open(
                        url,
                        'ImpresionTicketFiscal',
                        `width=${width},height=${height},top=${top},left=${left},status=no,toolbar=no,menubar=no,location=no,resizable=yes,scrollbars=yes`
                    );
                    if (popup) {
                        popup.focus();
                    }
                };

                window.addEventListener('abrir-ticket-flotante', function(event) {
                    const url = event.detail?.url || event.detail?.[0]?.url;
                    if (url) {
                        window.abrirTicketPdf(url);
                    }
                });
            })();
        </script>
        @stack('scripts')
    </body>
</html>
