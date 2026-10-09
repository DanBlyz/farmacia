<?php

namespace App\Livewire\Sincronizacion;

use App\Models\DependeActividad;
use App\Models\LeyendaFactura;
use App\Models\MotivoAnulacion;
use App\Models\ProductoServicio;
use App\Models\TipoDocumentoIdentidad;
use App\Models\TipoDocumentoSector;
use App\Models\TipoEventoSignificativo;
use App\Models\TipoMetodoPago;
use App\Models\TipoMoneda;
use App\Models\TipoPuntoVenta;
use App\Models\UnidadMedida;
use App\Services\SiatSincronizacionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Catalogos extends Component
{
    use WithPagination;

    public string $activeTab = 'TipoDocumentoSector';

    public int $perPage = 10;

    public string $search = '';

    public array $tabs = [
        'TipoDocumentoSector' => [
            'nombre' => 'Documentos Sector',
            'model' => TipoDocumentoSector::class,
            'col_codigo' => 'codigo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'TipoPuntoVenta' => [
            'nombre' => 'Puntos de Venta',
            'model' => TipoPuntoVenta::class,
            'col_codigo' => 'codigo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'UnidadMedida' => [
            'nombre' => 'Unidades de Medida',
            'model' => UnidadMedida::class,
            'col_codigo' => 'codigo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'TipoDocumentoIdentidad' => [
            'nombre' => 'Documentos de Identidad',
            'model' => TipoDocumentoIdentidad::class,
            'col_codigo' => 'codigo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'TipoMetodoPago' => [
            'nombre' => 'Métodos de Pago',
            'model' => TipoMetodoPago::class,
            'col_codigo' => 'tipo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'TipoMoneda' => [
            'nombre' => 'Tipos de Moneda',
            'model' => TipoMoneda::class,
            'col_codigo' => 'tipo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'MotivoAnulacion' => [
            'nombre' => 'Motivos de Anulación',
            'model' => MotivoAnulacion::class,
            'col_codigo' => 'codigo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'TipoEventoSignificativo' => [
            'nombre' => 'Eventos Significativos',
            'model' => TipoEventoSignificativo::class,
            'col_codigo' => 'codigo_clasificador',
            'col_desc' => 'descripcion',
        ],
        'DependeActividad' => [
            'nombre' => 'Actividades Económicas',
            'model' => DependeActividad::class,
            'col_codigo' => 'codigo_caeb',
            'col_desc' => 'descripcion',
        ],
        'ProductoServicio' => [
            'nombre' => 'Productos y Servicios SIN',
            'model' => ProductoServicio::class,
            'col_codigo' => 'codigo_producto',
            'col_desc' => 'descripcion_producto',
        ],
        'LeyendaFactura' => [
            'nombre' => 'Leyendas de Factura',
            'model' => LeyendaFactura::class,
            'col_codigo' => 'codigo_actividad',
            'col_desc' => 'descripcion_leyenda',
        ],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function cambiarTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs)) {
            $this->activeTab = $tab;
            $this->resetPage();
            $this->search = '';
        }
    }

    public function sincronizar(string $tipoCatalogo, SiatSincronizacionService $servicio): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.catalogos.sincronizar'), 403, 'No tienes permiso para sincronizar catálogos del SIAT.');

        $resultado = $servicio->sincronizarIndividual($tipoCatalogo);

        if ($resultado['success']) {
            $msgSuccess = "Sincronización exitosa: {$resultado['message']}";
            session()->flash('message', $msgSuccess);
            $this->dispatch('swal-alert', icon: 'success', title: '¡Catálogo Sincronizado!', message: $msgSuccess);
        } else {
            session()->flash('error', $resultado['message']);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error al Sincronizar', message: $resultado['message']);
        }
    }

    public function sincronizarTodos(SiatSincronizacionService $servicio): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.catalogos.sincronizar'), 403, 'No tienes permiso para sincronizar catálogos del SIAT.');

        $resultado = $servicio->sincronizarTodos();

        if ($resultado['success']) {
            $msgSuccess = "Sincronización total completada con éxito. Se actualizaron {$resultado['total_actualizados']} registros en total.";
            session()->flash('message', $msgSuccess);
            $this->dispatch('swal-alert', icon: 'success', title: '¡Todos los Catálogos Sincronizados!', message: $msgSuccess);
        } else {
            $msgParcial = "Sincronización completada con observaciones ({$resultado['total_actualizados']} registros actualizados). Revise los registros con error.";
            session()->flash('message', $msgParcial);
            $this->dispatch('swal-alert', icon: 'info', title: 'Sincronización Finalizada', message: $msgParcial);
        }
    }

    public function render(): View
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.catalogos.ver'), 403, 'No tienes permiso para ver los catálogos.');

        $activeInfo = $this->tabs[$this->activeTab] ?? $this->tabs['TipoDocumentoSector'];
        $modelClass = $activeInfo['model'];
        $colCodigo = $activeInfo['col_codigo'];
        $colDesc = $activeInfo['col_desc'] ?? 'descripcion';

        $items = $modelClass::query()
            ->when($this->search !== '', function ($query) use ($colCodigo, $colDesc) {
                $query->where(function ($q) use ($colCodigo, $colDesc) {
                    $q->where($colCodigo, 'like', '%'.trim($this->search).'%')
                        ->orWhere($colDesc, 'like', '%'.trim($this->search).'%');
                });
            })
            ->orderBy($colCodigo, 'asc')
            ->paginate($this->perPage);

        return view('livewire.sincronizacion.catalogos', [
            'items' => $items,
            'activeInfo' => $activeInfo,
        ]);
    }
}
