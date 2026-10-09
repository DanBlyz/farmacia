<?php

namespace App\Livewire\Administracion;

use App\Models\Rol;
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
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();
        $rol = Rol::findOrFail($id);
        $this->rolId = $rol->id;
        $this->nombre = (string) $rol->nombre;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:100|unique:roles,nombre,' . ($this->rolId ?? 'NULL') . ',id,deleted_at,NULL',
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
            session()->flash('message', 'Rol actualizado exitosamente.');
        } else {
            Rol::create([
                'nombre' => trim($this->nombre),
                'usuario_creador_id' => Auth::id(),
            ]);
            session()->flash('message', 'Rol registrado exitosamente.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $rol = Rol::findOrFail($id);
        $rol->update([
            'usuario_eliminador_id' => Auth::id(),
        ]);
        $rol->delete();

        session()->flash('message', 'Rol eliminado del sistema.');
        $this->dispatch('role-deleted', message: 'Rol eliminado del catálogo exitosamente.');
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

    public function render()
    {
        $roles = Rol::query()
            ->when($this->search !== '', function ($query) {
                $query->where('nombre', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.administracion.roles', [
            'roles' => $roles,
        ]);
    }
}
