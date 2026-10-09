<?php

namespace App\Livewire\Administracion;

use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Proveedores extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    // Estado del Formulario / Modal
    public bool $showModal = false;

    public ?int $proveedorId = null;

    public string $nombre = '';

    public string $direccion = '';

    public string $celular = '';

    public string $nit = '';

    public string $razon_social = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.proveedores.crear'), 403, 'No tiene permiso para crear proveedores.');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.proveedores.editar'), 403, 'No tiene permiso para editar proveedores.');
        $this->resetForm();
        $proveedor = Proveedor::findOrFail($id);
        $this->proveedorId = $proveedor->id;
        $this->nombre = (string) $proveedor->nombre;
        $this->direccion = (string) $proveedor->direccion;
        $this->celular = (string) $proveedor->celular;
        $this->nit = (string) $proveedor->nit;
        $this->razon_social = (string) $proveedor->razon_social;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->proveedorId) {
            abort_if(! Auth::user()->tienePermiso('administracion.proveedores.editar'), 403, 'No tiene permiso para editar proveedores.');
        } else {
            abort_if(! Auth::user()->tienePermiso('administracion.proveedores.crear'), 403, 'No tiene permiso para crear proveedores.');
        }

        $this->validate([
            'nombre' => 'required|string|max:150',
            'nit' => 'nullable|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'celular' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 150 caracteres.',
            'nit.max' => 'El NIT no debe exceder los 50 caracteres.',
            'razon_social.max' => 'La razón social no debe exceder los 150 caracteres.',
            'celular.max' => 'El celular no debe exceder los 50 caracteres.',
            'direccion.max' => 'La dirección no debe exceder los 255 caracteres.',
        ]);

        if ($this->proveedorId) {
            $proveedor = Proveedor::findOrFail($this->proveedorId);
            $proveedor->update([
                'nombre' => trim($this->nombre),
                'direccion' => trim($this->direccion),
                'celular' => trim($this->celular),
                'nit' => trim($this->nit),
                'razon_social' => trim($this->razon_social),
                'usuario_modificador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Proveedor actualizado exitosamente.',
            ]);
        } else {
            Proveedor::create([
                'nombre' => trim($this->nombre),
                'direccion' => trim($this->direccion),
                'celular' => trim($this->celular),
                'nit' => trim($this->nit),
                'razon_social' => trim($this->razon_social),
                'usuario_creador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Proveedor registrado exitosamente.',
            ]);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.proveedores.eliminar'), 403, 'No tiene permiso para eliminar proveedores.');

        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update([
            'usuario_eliminador_id' => Auth::id(),
        ]);
        $proveedor->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Eliminado!',
            'text' => 'Proveedor eliminado del catálogo exitosamente.',
        ]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['proveedorId', 'nombre', 'direccion', 'celular', 'nit', 'razon_social']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $proveedores = Proveedor::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%'.trim($this->search).'%')
                        ->orWhere('nit', 'like', '%'.trim($this->search).'%')
                        ->orWhere('razon_social', 'like', '%'.trim($this->search).'%')
                        ->orWhere('celular', 'like', '%'.trim($this->search).'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.administracion.proveedores', [
            'proveedores' => $proveedores,
        ]);
    }
}
