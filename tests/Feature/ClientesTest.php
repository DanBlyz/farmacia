<?php

use App\Livewire\Administracion\Clientes;
use App\Models\Cliente;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can visit the clientes index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('administracion.clientes'))
        ->assertStatus(200)
        ->assertSeeLivewire(Clientes::class);
});

test('can create a new cliente and close modal', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('nombres', 'Marco Antonio')
        ->set('ap_paterno', 'Solís')
        ->set('ap_materno', 'Rojas')
        ->set('cedula', '654321')
        ->set('complemento', '1B')
        ->set('nit', '1029384')
        ->set('razon_social', 'Solís S.R.L.')
        ->set('correo', 'marco@solis.com')
        ->set('numero_celular', '77665544')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('clientes', [
        'nombres' => 'Marco Antonio',
        'ap_paterno' => 'Solís',
        'ap_materno' => 'Rojas',
        'cedula' => '654321',
        'complemento' => '1B',
        'nit' => '1029384',
        'razon_social' => 'Solís S.R.L.',
        'correo' => 'marco@solis.com',
        'numero_celular' => '77665544',
        'usuario_creador_id' => $user->id,
    ]);
});

test('can search clientes by nombres, ap_paterno, cedula or nit', function () {
    $user = User::factory()->create();

    Cliente::create(['nombres' => 'Fernando', 'ap_paterno' => 'Alonso', 'cedula' => '111111']);
    Cliente::create(['nombres' => 'Carlos', 'ap_paterno' => 'Sainz', 'cedula' => '222222']);

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->set('search', 'Alonso')
        ->assertSee('Fernando')
        ->assertDontSee('Sainz');
});

test('can edit an existing cliente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'nombres' => 'Pedro',
        'ap_paterno' => 'Gómez',
        'cedula' => '123456',
    ]);

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->call('edit', $cliente->id)
        ->assertSet('clienteId', $cliente->id)
        ->assertSet('nombres', 'Pedro')
        ->set('nombres', 'Pedro Pablo')
        ->set('cedula', '999999')
        ->call('save')
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'nombres' => 'Pedro Pablo',
        'cedula' => '999999',
        'usuario_modificador_id' => $user->id,
    ]);
});

test('can soft delete a cliente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'nombres' => 'Cliente Por Eliminar',
    ]);

    Livewire::actingAs($user)
        ->test(Clientes::class)
        ->call('delete', $cliente->id);

    $this->assertSoftDeleted('clientes', [
        'id' => $cliente->id,
        'usuario_eliminador_id' => $user->id,
    ]);
});
