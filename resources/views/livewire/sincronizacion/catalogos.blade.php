<div class="space-y-6">
    <x-slot name="header">
        Catalogos
    </x-slot>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sincronización de Catálogos SIAT
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Mantenga actualizados los catálogos paramétricos del sistema de facturación en línea.
            </p>
        </div>
    </div>

    <!-- Alpine.js Tabs -->
    <div x-data="{ activeTab: 'TipoDocumentoSector' }" class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden">
        
        <!-- Tab Navigation -->
        <div class="flex flex-nowrap overflow-x-auto border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 hide-scrollbar">
            @php
                $tabs = [
                    'TipoDocumentoSector' => 'Sector',
                    'TipoPuntoVenta' => 'Puntos de Venta',
                    'UnidadMedida' => 'Unidades de Medida',
                    'TipoDocumentoIdentidad' => 'Doc. Identidad',
                    'TipoMetodoPago' => 'Métodos de Pago',
                    'TipoMoneda' => 'Monedas',
                    'MotivoAnulacion' => 'Anulación',
                    'TipoEventoSignificativo' => 'Eventos Significativos'
                ];
            @endphp
            
            @foreach($tabs as $key => $label)
                <button 
                    @click="activeTab = '{{ $key }}'" 
                    :class="activeTab === '{{ $key }}' ? 'border-teal-600 text-teal-600 bg-teal-50 dark:bg-teal-950/40 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 font-semibold'"
                    class="px-5 py-4 text-sm whitespace-nowrap border-b-2 transition-all focus:outline-none cursor-pointer">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Tab Contents -->
        <div class="p-6">
            @foreach($tabs as $key => $label)
                <div x-show="activeTab === '{{ $key }}'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Catálogo de {{ $label }}</h3>
                        
                        @permiso('sincronizacion.catalogos.sincronizar')
                            <button type="button" 
                                    wire:click="sincronizar('{{ $key }}')"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-2xs flex items-center gap-2 transition-all focus:outline-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="sincronizar('{{ $key }}')" class="flex items-center gap-2">
                                    <i class="fas fa-sync-alt text-xs"></i>
                                    <span>Sincronizar {{ $label }}</span>
                                </span>
                                <span wire:loading wire:target="sincronizar('{{ $key }}')" class="flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin text-xs"></i>
                                    <span>Sincronizando...</span>
                                </span>
                            </button>
                        @endpermiso
                    </div>

                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-2xl max-h-[60vh]">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800">
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase">
                                    <th class="py-3 px-4 w-24 text-center">Código</th>
                                    <th class="py-3 px-4">Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @php
                                    $collection = ${lcfirst($key)};
                                @endphp
                                @forelse($collection as $item)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3 px-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                            {{ in_array($key, ['TipoMoneda', 'TipoMetodoPago']) ? $item->tipo_clasificador : $item->codigo_clasificador }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                                            {{ $item->descripcion }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                                            No hay registros en el catálogo. Por favor, realice la sincronización con SIAT.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            window.addEventListener('swal-alert', (event) => {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                Swal.fire({
                    title: data.title || (data.icon === 'error' ? 'Atención SIAT' : '¡Éxito!'),
                    html: data.message,
                    icon: data.icon || 'info',
                    confirmButtonColor: data.icon === 'error' ? '#e11d48' : '#0d9488',
                    confirmButtonText: '<svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Entendido',
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl font-sans p-6 text-left',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-md transition-all'
                    }
                });
            });
        });
    </script>
    @endpush
    
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</div>
