<!-- Footer AdminLTE Style -->
<footer class="h-14 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 text-xs text-slate-500 dark:text-slate-400 shrink-0 select-none">
    <div>
        <strong>Copyright &copy; {{ date('Y') }} <a href="{{ route('dashboard') }}" class="text-teal-600 dark:text-teal-400 hover:underline font-semibold">Farma ERP</a>.</strong>
        <span class="hidden sm:inline">Todos los derechos reservados.</span>
    </div>
    <div class="flex items-center gap-4">
        <span class="hidden md:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold border border-emerald-500/20">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            SIAT & Base de Datos OK
        </span>
        <div class="font-mono text-slate-600 dark:text-slate-300">
            <b>Versión</b> 1.0.0
        </div>
    </div>
</footer>
