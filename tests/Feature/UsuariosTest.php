<?php

use App\Livewire\Administracion\Usuarios;
use App\Models\PuntoVenta;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('unauthorized users cannot visit the usuarios index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('administracion.usuarios'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the usuarios index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('administracion.usuarios'))
        ->assertStatus(200)
        ->assertSeeLivewire(Usuarios::class);
});

test('can create a new usuario with password auto-assigned from cedula and linked to sucursal point of sale', function () {
    $admin = User::factory()->admin()->create();
    $rol = Rol::create(['nombre' => 'Cajero']);
    $sucursal = Sucursal::create(['nombre' => 'Sucursal Central', 'codigo_sucursal' => 0]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'codigo' => 1,
        'nombre' => 'Ventanilla 1',
        'tipo' => 'VENTANILLA',
        'codigo_ambiente' => 2,
    ]);

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('nombres', 'Gabriel')
        ->set('ap_paterno', 'Torres')
        ->set('ap_materno', 'Ríos')
        ->set('email', 'gabriel@farmacia.com')
        ->set('cedula', '44556677')
        ->set('celular', '71122334')
        ->set('direccion', 'Av. 6 de Agosto')
        ->set('rol_id', $rol->id)
        ->set('sucursal_id', $sucursal->id)
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $newUser = User::where('email', 'gabriel@farmacia.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->punto_venta_id)->toBe($puntoVenta->id);
    expect($newUser->rol_id)->toBe($rol->id);
    expect(Hash::check('44556677', $newUser->password))->toBeTrue();
});

test('unauthorized users cannot create, edit or delete usuarios', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create(['name' => 'Usuario Protegido']);

    Livewire::actingAs($user)
        ->test(Usuarios::class)
        ->call('create')
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Usuarios::class)
        ->call('edit', $targetUser->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Usuarios::class)
        ->call('delete', $targetUser->id)
        ->assertStatus(403);
});

test('can search usuarios by name, email or cedula', function () {
    $admin = User::factory()->admin()->create();

    User::factory()->create(['name' => 'Mario Valenzuela', 'cedula' => '99887711']);
    User::factory()->create(['name' => 'Soledad Pastorutti', 'cedula' => '33221100']);

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->set('search', 'Valenzuela')
        ->assertSee('Mario Valenzuela')
        ->assertDontSee('Soledad Pastorutti');
});

test('can edit an existing usuario without changing their password', function () {
    $admin = User::factory()->admin()->create();
    $rol = Rol::create(['nombre' => 'Administrador']);
    $sucursal = Sucursal::create(['nombre' => 'Sucursal Norte', 'codigo_sucursal' => 1]);
    $puntoVenta = PuntoVenta::create([
        'sucursal_id' => $sucursal->id,
        'codigo' => 1,
        'nombre' => 'Ventanilla Norte',
        'tipo' => 'VENTANILLA',
        'codigo_ambiente' => 2,
    ]);

    $usuario = User::factory()->create([
        'name' => 'Usuario Original',
        'nombres' => 'Usuario Original',
        'cedula' => '101010',
        'rol_id' => $rol->id,
        'punto_venta_id' => $puntoVenta->id,
    ]);
    $oldPasswordHash = $usuario->password;

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('edit', $usuario->id)
        ->assertSet('usuarioId', $usuario->id)
        ->assertSet('sucursal_id', $sucursal->id)
        ->set('nombres', 'Usuario Actualizado')
        ->set('cedula', '202020')
        ->call('save')
        ->assertSet('showModal', false);

    $usuario->refresh();
    expect($usuario->nombres)->toBe('Usuario Actualizado');
    expect($usuario->cedula)->toBe('202020');
    expect($usuario->password)->toBe($oldPasswordHash);
});

test('can soft delete another user but prevents deleting self', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->create(['name' => 'Otro Usuario']);

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('delete', $otherUser->id);

    $this->assertSoftDeleted('users', [
        'id' => $otherUser->id,
        'usuario_eliminador_id' => $admin->id,
    ]);

    // Intentar eliminarse a sí mismo no debe funcionar
    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('delete', $admin->id)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'deleted_at' => null,
    ]);
});

test('can reset a user password via modal', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create([
        'name' => 'Usuario Para Clave Nueva',
        'nombres' => 'Usuario Para Clave Nueva',
    ]);

    expect(Hash::check('NuevaClaveSegura123', $targetUser->password))->toBeFalse();

    Livewire::actingAs($admin)
        ->test(Usuarios::class)
        ->call('openPasswordModal', $targetUser->id)
        ->assertSet('showPasswordModal', true)
        ->assertSet('resetUsuarioId', $targetUser->id)
        ->set('newPassword', 'NuevaClaveSegura123')
        ->call('saveNewPassword')
        ->assertSet('showPasswordModal', false)
        ->assertHasNoErrors();

    $targetUser->refresh();
    expect(Hash::check('NuevaClaveSegura123', $targetUser->password))->toBeTrue();
    expect($targetUser->usuario_modificador_id)->toBe($admin->id);
});
