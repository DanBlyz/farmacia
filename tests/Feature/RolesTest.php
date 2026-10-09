<?php

use App\Livewire\Administracion\Roles;
use App\Models\Rol;
use App\Models\User;
use Livewire\Livewire;

test('unauthorized users cannot visit the roles index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('administracion.roles'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the roles index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('administracion.roles'))
        ->assertStatus(200)
        ->assertSeeLivewire(Roles::class);
});

test('can create a new role and close modal', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('nombre', 'Administrador General')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('roles', [
        'nombre' => 'Administrador General',
        'usuario_creador_id' => $user->id,
    ]);
});

test('unauthorized users cannot create, edit or delete roles', function () {
    $user = User::factory()->create();
    $rol = Rol::create(['nombre' => 'Rol Protegido']);

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->call('create')
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->call('edit', $rol->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->call('delete', $rol->id)
        ->assertStatus(403);
});

test('can search roles by name', function () {
    $user = User::factory()->admin()->create();

    Rol::create(['nombre' => 'Cajero Especial']);
    Rol::create(['nombre' => 'Médico Auditor']);

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->set('search', 'Cajero')
        ->assertSee('Cajero Especial')
        ->assertDontSee('Médico Auditor');
});

test('can edit an existing role', function () {
    $user = User::factory()->admin()->create();
    $rol = Rol::create(['nombre' => 'Farmacéutico Auxiliar']);

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->call('edit', $rol->id)
        ->assertSet('rolId', $rol->id)
        ->assertSet('nombre', 'Farmacéutico Auxiliar')
        ->set('nombre', 'Farmacéutico Jefe')
        ->call('save')
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('roles', [
        'id' => $rol->id,
        'nombre' => 'Farmacéutico Jefe',
        'usuario_modificador_id' => $user->id,
    ]);
});

test('can soft delete a role', function () {
    $user = User::factory()->admin()->create();
    $rol = Rol::create(['nombre' => 'Personal Temporal']);

    Livewire::actingAs($user)
        ->test(Roles::class)
        ->call('delete', $rol->id);

    $this->assertSoftDeleted('roles', [
        'id' => $rol->id,
        'usuario_eliminador_id' => $user->id,
    ]);
});
