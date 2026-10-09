<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Administracion\Clientes;
use App\Livewire\Administracion\Proveedores;
use App\Livewire\Administracion\Roles;
use App\Livewire\Administracion\Sucursales;
use App\Livewire\Administracion\Usuarios;
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

    // Módulos del Sistema ERP

    Route::prefix('/administracion')->group(function () {
        Route::get('/roles', Roles::class)->name('administracion.roles');
        Route::get('/sucursales', Sucursales::class)->name('administracion.sucursales');
        Route::get('/proveedores', Proveedores::class)->name('administracion.proveedores');
        Route::get('/clientes', Clientes::class)->name('administracion.clientes');
        Route::get('/usuarios', Usuarios::class)->name('administracion.usuarios');
    });

    Route::prefix('/sincronizacion')->group(function () {
        Route::get('/catalogos', Catalogos::class)->name('sincronizacion.catalogos');
        Route::get('/eventos-significativos', EventosSignificativos::class)->name('sincronizacion.eventos');
    });
});

require __DIR__ . '/auth.php';
