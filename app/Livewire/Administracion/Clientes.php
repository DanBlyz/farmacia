<?php

namespace App\Livewire\Administracion;

use App\Models\Cliente;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Clientes extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    // Estado del Formulario / Modal
    public bool $showModal = false;

    public ?int $clienteId = null;

    public string $nombres = '';

    public string $ap_paterno = '';

    public string $ap_materno = '';

    public string $cedula = '';

    public string $complemento = '';

    public string $nit = '';

    public string $razon_social = '';

    public string $correo = '';

    public string $numero_celular = '';

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
        abort_if(! Auth::user()->tienePermiso('administracion.clientes.crear'), 403, 'No tiene permiso para crear clientes.');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.clientes.editar'), 403, 'No tiene permiso para editar clientes.');
        $this->resetForm();
        $cliente = Cliente::findOrFail($id);
        $this->clienteId = $cliente->id;
        $this->nombres = (string) $cliente->nombres;
        $this->ap_paterno = (string) $cliente->ap_paterno;
        $this->ap_materno = (string) $cliente->ap_materno;
        $this->cedula = (string) $cliente->cedula;
        $this->complemento = (string) $cliente->complemento;
        $this->nit = (string) $cliente->nit;
        $this->razon_social = (string) $cliente->razon_social;
        $this->correo = (string) $cliente->correo;
        $this->numero_celular = (string) $cliente->numero_celular;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->clienteId) {
            abort_if(! Auth::user()->tienePermiso('administracion.clientes.editar'), 403, 'No tiene permiso para editar clientes.');
        } else {
            abort_if(! Auth::user()->tienePermiso('administracion.clientes.crear'), 403, 'No tiene permiso para crear clientes.');
        }

        $this->validate([
            'nombres' => 'required|string|max:150',
            'ap_paterno' => 'nullable|string|max:100',
            'ap_materno' => 'nullable|string|max:100',
            'cedula' => 'nullable|string|max:50',
            'complemento' => 'nullable|string|max:10',
            'nit' => 'nullable|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'correo' => 'nullable|email|max:150',
            'numero_celular' => 'nullable|string|max:50',
        ], [
            'nombres.required' => 'El nombre del cliente es obligatorio.',
            'nombres.max' => 'El nombre no debe exceder los 150 caracteres.',
            'ap_paterno.max' => 'El apellido paterno no debe exceder los 100 caracteres.',
            'ap_materno.max' => 'El apellido materno no debe exceder los 100 caracteres.',
            'cedula.max' => 'La cédula no debe exceder los 50 caracteres.',
            'complemento.max' => 'El complemento no debe exceder los 10 caracteres.',
            'nit.max' => 'El NIT no debe exceder los 50 caracteres.',
            'razon_social.max' => 'La razón social no debe exceder los 150 caracteres.',
            'correo.email' => 'El formato del correo electrónico no es válido.',
            'correo.max' => 'El correo no debe exceder los 150 caracteres.',
            'numero_celular.max' => 'El número de celular no debe exceder los 50 caracteres.',
        ]);

        if ($this->clienteId) {
            $cliente = Cliente::findOrFail($this->clienteId);
            $cliente->update([
                'nombres' => trim($this->nombres),
                'ap_paterno' => trim($this->ap_paterno),
                'ap_materno' => trim($this->ap_materno),
                'cedula' => trim($this->cedula),
                'complemento' => trim($this->complemento),
                'nit' => trim($this->nit),
                'razon_social' => trim($this->razon_social),
                'correo' => trim($this->correo),
                'numero_celular' => trim($this->numero_celular),
                'usuario_modificador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Cliente actualizado exitosamente.',
            ]);
        } else {
            Cliente::create([
                'nombres' => trim($this->nombres),
                'ap_paterno' => trim($this->ap_paterno),
                'ap_materno' => trim($this->ap_materno),
                'cedula' => trim($this->cedula),
                'complemento' => trim($this->complemento),
                'nit' => trim($this->nit),
                'razon_social' => trim($this->razon_social),
                'correo' => trim($this->correo),
                'numero_celular' => trim($this->numero_celular),
                'usuario_creador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Cliente registrado exitosamente.',
            ]);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.clientes.eliminar'), 403, 'No tiene permiso para eliminar clientes.');

        $cliente = Cliente::findOrFail($id);
        $cliente->update([
            'usuario_eliminador_id' => Auth::id(),
        ]);
        $cliente->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Eliminado!',
            'text' => 'Cliente eliminado del catálogo exitosamente.',
        ]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'clienteId', 'nombres', 'ap_paterno', 'ap_materno',
            'cedula', 'complemento', 'nit', 'razon_social',
            'correo', 'numero_celular',
        ]);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $clientes = Cliente::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', '%'.trim($this->search).'%')
                        ->orWhere('ap_paterno', 'like', '%'.trim($this->search).'%')
                        ->orWhere('ap_materno', 'like', '%'.trim($this->search).'%')
                        ->orWhere('cedula', 'like', '%'.trim($this->search).'%')
                        ->orWhere('nit', 'like', '%'.trim($this->search).'%')
                        ->orWhere('razon_social', 'like', '%'.trim($this->search).'%')
                        ->orWhere('numero_celular', 'like', '%'.trim($this->search).'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.administracion.clientes', [
            'clientes' => $clientes,
        ]);
    }
}
