<?php

namespace App\Livewire\Administracion;

use App\Models\Rol;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Roles extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    // Estado del Formulario / Modal
    public bool $showModal = false;

    public ?int $rolId = null;

    public string $nombre = '';

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
        abort_if(! Auth::user()->tienePermiso('administracion.roles.crear'), 403, 'No tiene permiso para crear roles.');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.roles.editar'), 403, 'No tiene permiso para editar roles.');
        $this->resetForm();
        $rol = Rol::findOrFail($id);
        $this->rolId = $rol->id;
        $this->nombre = (string) $rol->nombre;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->rolId) {
            abort_if(! Auth::user()->tienePermiso('administracion.roles.editar'), 403, 'No tiene permiso para editar roles.');
        } else {
            abort_if(! Auth::user()->tienePermiso('administracion.roles.crear'), 403, 'No tiene permiso para crear roles.');
        }

        $this->validate([
            'nombre' => 'required|string|max:100|unique:roles,nombre,'.($this->rolId ?? 'NULL').',id,deleted_at,NULL',
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'nombre.unique' => 'Este nombre de rol ya se encuentra registrado.',
        ]);

        if ($this->rolId) {
            $rol = Rol::findOrFail($this->rolId);
            $rol->update([
                'nombre' => trim($this->nombre),
                'usuario_modificador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Rol actualizado exitosamente.',
            ]);
        } else {
            Rol::create([
                'nombre' => trim($this->nombre),
                'usuario_creador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Rol registrado exitosamente.',
            ]);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.roles.eliminar'), 403, 'No tiene permiso para eliminar roles.');

        $rol = Rol::findOrFail($id);
        $rol->update([
            'usuario_eliminador_id' => Auth::id(),
        ]);
        $rol->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Eliminado!',
            'text' => 'Rol eliminado del catálogo exitosamente.',
        ]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['rolId', 'nombre']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $roles = Rol::query()
            ->when($this->search !== '', function ($query) {
                $query->where('nombre', 'like', '%'.trim($this->search).'%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.administracion.roles', [
            'roles' => $roles,
        ]);
    }
}
