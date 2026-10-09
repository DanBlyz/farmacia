<?php

namespace App\Livewire\Administracion;

use App\Models\PuntoVenta;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Usuarios extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    // Estado del Formulario / Modal
    public bool $showModal = false;

    public ?int $usuarioId = null;

    public string $email = '';

    public string $nombres = '';

    public string $ap_paterno = '';

    public string $ap_materno = '';

    public ?int $rol_id = null;

    public ?int $sucursal_id = null;

    public string $cedula = '';

    public string $direccion = '';

    public string $celular = '';

    // Estado del Modal de Reseteo de Contraseña
    public bool $showPasswordModal = false;

    public ?int $resetUsuarioId = null;

    public string $newPassword = '';

    public string $resetUsuarioNombre = '';

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
        abort_if(! Auth::user()->tienePermiso('administracion.usuarios.crear'), 403, 'No tiene permiso para crear usuarios.');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.usuarios.editar'), 403, 'No tiene permiso para editar usuarios.');
        $this->resetForm();
        $usuario = User::with('puntoVenta')->findOrFail($id);
        $this->usuarioId = $usuario->id;
        $this->email = (string) $usuario->email;
        $this->nombres = (string) $usuario->nombres;
        $this->ap_paterno = (string) $usuario->ap_paterno;
        $this->ap_materno = (string) $usuario->ap_materno;
        $this->rol_id = $usuario->rol_id ? (int) $usuario->rol_id : null;
        $this->sucursal_id = $usuario->puntoVenta ? (int) $usuario->puntoVenta->sucursal_id : null;
        $this->cedula = (string) $usuario->cedula;
        $this->direccion = (string) $usuario->direccion;
        $this->celular = (string) $usuario->celular;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->usuarioId) {
            abort_if(! Auth::user()->tienePermiso('administracion.usuarios.editar'), 403, 'No tiene permiso para editar usuarios.');
        } else {
            abort_if(! Auth::user()->tienePermiso('administracion.usuarios.crear'), 403, 'No tiene permiso para crear usuarios.');
        }

        $this->validate([
            'email' => 'required|email|max:255|unique:users,email,'.($this->usuarioId ?? 'NULL').',id,deleted_at,NULL',
            'nombres' => 'required|string|max:150',
            'ap_paterno' => 'nullable|string|max:100',
            'ap_materno' => 'nullable|string|max:100',
            'rol_id' => 'required|exists:roles,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'cedula' => 'required|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:50',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no deben exceder los 150 caracteres.',
            'rol_id.required' => 'Debes seleccionar un rol para el usuario.',
            'rol_id.exists' => 'El rol seleccionado no es válido.',
            'sucursal_id.required' => 'Debes asignar una sucursal al usuario.',
            'sucursal_id.exists' => 'La sucursal seleccionada no es válida.',
            'cedula.required' => 'La cédula de identidad es obligatoria (se utilizará como contraseña en nuevos usuarios).',
            'cedula.max' => 'La cédula no debe exceder los 50 caracteres.',
            'direccion.max' => 'La dirección no debe exceder los 255 caracteres.',
            'celular.max' => 'El celular no debe exceder los 50 caracteres.',
        ]);

        // Relacionamos con el primer punto de venta de la sucursal seleccionada
        $puntoVenta = PuntoVenta::where('sucursal_id', $this->sucursal_id)->orderBy('id')->first();
        if (! $puntoVenta) {
            $sucursal = Sucursal::findOrFail($this->sucursal_id);
            $puntoVenta = PuntoVenta::create([
                'usuario_creador_id' => Auth::id(),
                'sucursal_id' => $sucursal->id,
                'codigo' => 0,
                'nombre' => 'PRIMER PUNTO VENTA POR DEFECTO ('.$sucursal->nombre.')',
                'tipo' => 'VENTANILLA INICIAL POR DEFECTO',
                'codigo_ambiente' => 2,
            ]);
        }

        $nombreCompleto = trim($this->nombres.' '.$this->ap_paterno.' '.$this->ap_materno);

        if ($this->usuarioId) {
            $usuario = User::findOrFail($this->usuarioId);
            $usuario->update([
                'name' => $nombreCompleto ?: $this->nombres,
                'email' => trim($this->email),
                'nombres' => trim($this->nombres),
                'ap_paterno' => trim($this->ap_paterno),
                'ap_materno' => trim($this->ap_materno),
                'rol_id' => $this->rol_id,
                'punto_venta_id' => $puntoVenta->id,
                'cedula' => trim($this->cedula),
                'direccion' => trim($this->direccion),
                'celular' => trim($this->celular),
                'usuario_modificador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Usuario actualizado exitosamente.',
            ]);
        } else {
            User::create([
                'name' => $nombreCompleto ?: $this->nombres,
                'email' => trim($this->email),
                'password' => Hash::make(trim($this->cedula)),
                'nombres' => trim($this->nombres),
                'ap_paterno' => trim($this->ap_paterno),
                'ap_materno' => trim($this->ap_materno),
                'rol_id' => $this->rol_id,
                'punto_venta_id' => $puntoVenta->id,
                'cedula' => trim($this->cedula),
                'direccion' => trim($this->direccion),
                'celular' => trim($this->celular),
                'usuario_creador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Usuario registrado exitosamente (Contraseña asignada según Cédula).',
            ]);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.usuarios.eliminar'), 403, 'No tiene permiso para eliminar usuarios.');

        if ($id === Auth::id()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción Denegada',
                'text' => 'No puedes eliminar tu propia cuenta de usuario mientras estás en sesión.',
            ]);

            return;
        }

        $usuario = User::findOrFail($id);
        $usuario->update([
            'usuario_eliminador_id' => Auth::id(),
        ]);
        $usuario->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Eliminado!',
            'text' => 'Usuario eliminado del catálogo exitosamente.',
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
            'usuarioId', 'email', 'nombres', 'ap_paterno', 'ap_materno',
            'rol_id', 'sucursal_id', 'cedula', 'direccion', 'celular',
        ]);
        $this->resetErrorBag();
    }

    public function openPasswordModal(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.usuarios.editar'), 403, 'No tiene permiso para modificar contraseñas.');
        $usuario = User::findOrFail($id);
        $this->resetUsuarioId = $usuario->id;
        $this->resetUsuarioNombre = trim($usuario->nombres.' '.$usuario->ap_paterno.' '.$usuario->ap_materno) ?: $usuario->name;
        $this->newPassword = '';
        $this->resetErrorBag();
        $this->showPasswordModal = true;
    }

    public function saveNewPassword(): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.usuarios.editar'), 403, 'No tiene permiso para modificar contraseñas.');

        $this->validate([
            'newPassword' => 'required|string|min:4|max:100',
        ], [
            'newPassword.required' => 'La nueva contraseña es obligatoria.',
            'newPassword.min' => 'La contraseña debe tener al menos 4 caracteres.',
            'newPassword.max' => 'La contraseña no debe exceder los 100 caracteres.',
        ]);

        $usuario = User::findOrFail($this->resetUsuarioId);
        $usuario->update([
            'password' => Hash::make($this->newPassword),
            'usuario_modificador_id' => Auth::id(),
        ]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Contraseña Actualizada!',
            'text' => 'Contraseña restablecida exitosamente para el usuario '.$this->resetUsuarioNombre.'.',
        ]);

        $this->closePasswordModal();
    }

    public function closePasswordModal(): void
    {
        $this->showPasswordModal = false;
        $this->reset(['resetUsuarioId', 'newPassword', 'resetUsuarioNombre']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $usuarios = User::query()
            ->with(['rol', 'puntoVenta.sucursal'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.trim($this->search).'%')
                        ->orWhere('nombres', 'like', '%'.trim($this->search).'%')
                        ->orWhere('ap_paterno', 'like', '%'.trim($this->search).'%')
                        ->orWhere('ap_materno', 'like', '%'.trim($this->search).'%')
                        ->orWhere('email', 'like', '%'.trim($this->search).'%')
                        ->orWhere('cedula', 'like', '%'.trim($this->search).'%')
                        ->orWhere('celular', 'like', '%'.trim($this->search).'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.administracion.usuarios', [
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('nombre')->get(),
            'sucursales' => Sucursal::orderBy('nombre')->get(),
        ]);
    }
}
