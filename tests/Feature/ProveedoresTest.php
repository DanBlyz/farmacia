<?php

use App\Livewire\Administracion\Proveedores;
use App\Models\Proveedor;
use App\Models\User;
use Livewire\Livewire;

test('unauthorized users cannot visit the proveedores index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('administracion.proveedores'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the proveedores index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('administracion.proveedores'))
        ->assertStatus(200)
        ->assertSeeLivewire(Proveedores::class);
});

test('can create a new proveedor and close modal', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('nombre', 'Carlos Mendoza')
        ->set('razon_social', 'Laboratorio Bagó')
        ->set('nit', '10293847')
        ->set('celular', '71234567')
        ->set('direccion', 'Av. Arce #100')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('proveedores', [
        'nombre' => 'Carlos Mendoza',
        'razon_social' => 'Laboratorio Bagó',
        'nit' => '10293847',
        'celular' => '71234567',
        'direccion' => 'Av. Arce #100',
        'usuario_creador_id' => $user->id,
    ]);
});

test('unauthorized users cannot create, edit or delete proveedores', function () {
    $user = User::factory()->create();
    $proveedor = Proveedor::create(['nombre' => 'Proveedor Protegido']);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('create')
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('edit', $proveedor->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('delete', $proveedor->id)
        ->assertStatus(403);
});

test('can search proveedores by name, nit, celular or razon_social', function () {
    $user = User::factory()->admin()->create();

    Proveedor::create(['nombre' => 'Droguería Inti', 'nit' => '99887766']);
    Proveedor::create(['nombre' => 'Laboratorio Vita', 'nit' => '11223344']);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->set('search', 'Inti')
        ->assertSee('Droguería Inti')
        ->assertDontSee('Laboratorio Vita');
});

test('can edit an existing proveedor', function () {
    $user = User::factory()->admin()->create();
    $proveedor = Proveedor::create([
        'nombre' => 'Nombre Viejo',
        'nit' => '1000',
    ]);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('edit', $proveedor->id)
        ->assertSet('proveedorId', $proveedor->id)
        ->assertSet('nombre', 'Nombre Viejo')
        ->set('nombre', 'Nombre Actualizado')
        ->set('nit', '2000')
        ->call('save')
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('proveedores', [
        'id' => $proveedor->id,
        'nombre' => 'Nombre Actualizado',
        'nit' => '2000',
        'usuario_modificador_id' => $user->id,
    ]);
});

test('can soft delete a proveedor', function () {
    $user = User::factory()->admin()->create();
    $proveedor = Proveedor::create([
        'nombre' => 'Proveedor Por Eliminar',
    ]);

    Livewire::actingAs($user)
        ->test(Proveedores::class)
        ->call('delete', $proveedor->id);

    $this->assertSoftDeleted('proveedores', [
        'id' => $proveedor->id,
        'usuario_eliminador_id' => $user->id,
    ]);
});
