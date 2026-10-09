<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Farma') }} - Portal Integrado de Farmacia</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles & Scripts -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="{{ asset('js/app.js') }}" defer></script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen selection:bg-teal-500 selection:text-white">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Panel Izquierdo: Branding & Contexto Farmacéutico -->
            <div class="hidden lg:flex lg:w-7/12 bg-slate-900 text-white p-12 flex-col justify-between relative overflow-hidden border-r border-slate-800">
                <!-- Luces decorativas de fondo -->
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-12 right-12 w-96 h-96 bg-teal-500/5 rounded-full blur-3xl pointer-events-none"></div>
                
                <!-- Patrón sutil médico / farmacia -->
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>

                <!-- Cabecera Logo -->
                <div class="relative z-10 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-600 flex items-center justify-center shadow-2xs font-bold text-2xl tracking-wider text-white">
                        <i class="fas fa-prescription-bottle-alt text-xl text-white"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-bold tracking-tight text-white block leading-tight">FARMA</span>
                        <span class="text-xs font-medium text-teal-300 tracking-widest uppercase block">Sistema Integral ERP</span>
                    </div>
                </div>

                <!-- Hero Central -->
                <div class="relative z-10 my-auto max-w-xl py-12">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-300 text-xs font-semibold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                        Tecnología Médica & Pos SIAT
                    </div>
                    <h1 class="text-4xl xl:text-5xl font-extrabold text-white tracking-tight leading-tight mb-6">
                        Gestión inteligente para tu salud y bienestar
                    </h1>
                    <p class="text-slate-300 text-base xl:text-lg leading-relaxed mb-8">
                        Control exhaustivo de inventarios, lotes y vencimientos de medicamentos con facturación en línea rápida, segura y altamente confiable.
                    </p>

                    <!-- Píldoras de características -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/20 flex items-center justify-center text-teal-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span class="text-sm font-medium text-slate-200">Dispensación Rápida</span>
                        </div>
                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <span class="text-sm font-medium text-slate-200">Control de Lotes</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Izquierdo -->
                <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 border-t border-white/10 pt-6">
                    <p>&copy; {{ date('Y') }} Farma ERP. Todos los derechos reservados.</p>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Servidor En Línea
                        </span>
                        <span>v{{ app()->version() }}</span>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Área de Formulario ($slot) -->
            <div class="w-full lg:w-5/12 flex items-center justify-center p-6 sm:p-12 relative overflow-hidden">
                <!-- Luz de fondo en móvil/escritorio -->
                <div class="absolute -top-32 -right-32 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="w-full max-w-md bg-white dark:bg-slate-900 p-8 sm:p-10 rounded-3xl shadow-2xl shadow-slate-200/60 dark:shadow-none border border-slate-100 dark:border-slate-800 relative z-10">
                    <!-- Logo visible únicamente en móvil -->
                    <div class="flex lg:hidden items-center justify-center gap-2 mb-8">
                        <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center shadow-2xs text-white">
                            <i class="fas fa-prescription-bottle-alt text-base text-white"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">FARMA</span>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
