<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Administracion\Clientes;
use App\Livewire\Administracion\Proveedores;
use App\Livewire\Administracion\Roles;
use App\Livewire\Administracion\Sucursales;
use App\Livewire\Administracion\Usuarios;
use App\Livewire\Inventario\Kardex;
use App\Livewire\Inventario\Lotes;
use App\Livewire\Inventario\Productos;
use App\Livewire\Sincronizacion\Catalogos;
use App\Livewire\Sincronizacion\EventosSignificativos;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulos del Sistema
    Route::prefix('administracion')->as('administracion.')->group(function () {
        Route::get('/sucursales', Sucursales::class)->middleware('permiso:administracion.sucursales.ver')->name('sucursales');
        Route::get('/usuarios', Usuarios::class)->middleware('permiso:administracion.usuarios.ver')->name('usuarios');
        Route::get('/roles', Roles::class)->middleware('permiso:administracion.roles.ver')->name('roles');
        Route::get('/clientes', Clientes::class)->middleware('permiso:administracion.clientes.ver')->name('clientes');
        Route::get('/proveedores', Proveedores::class)->middleware('permiso:administracion.proveedores.ver')->name('proveedores');
    });

    Route::prefix('sincronizacion')->as('sincronizacion.')->group(function () {
        Route::get('/catalogos', Catalogos::class)->middleware('permiso:sincronizacion.catalogos.ver')->name('catalogos');
        Route::get('/eventos-significativos', EventosSignificativos::class)->middleware('permiso:sincronizacion.eventos.ver')->name('eventos');
    });

    Route::prefix('inventario')->as('inventario.')->group(function () {
        Route::get('/productos', Productos::class)->middleware('permiso:inventario.productos.ver')->name('productos');
        Route::get('/lotes', Lotes::class)->middleware('permiso:inventario.lotes.ver')->name('lotes');
        Route::get('/kardex', Kardex::class)->middleware('permiso:inventario.kardex.ver')->name('kardex');
    });
});

require __DIR__.'/auth.php';
