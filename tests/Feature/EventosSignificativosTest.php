<?php

use App\Livewire\Sincronizacion\EventosSignificativos;
use App\Models\EventoSignificativo;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoEventoSignificativo;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $sucursal = Sucursal::firstOrCreate(['id' => 1], [
        'nombre' => 'Casa Matriz',
        'codigo_sucursal' => 0,
        'direccion' => 'Av. Central 123',
        'estado' => 'ACTIVO',
    ]);

    PuntoVenta::firstOrCreate(['id' => 1], [
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Punto de Venta 1',
        'codigo' => 1,
        'estado' => 'ACTIVO',
    ]);

    TipoEventoSignificativo::firstOrCreate(['id' => 1], [
        'codigo_clasificador' => 1,
        'descripcion' => 'CORTE DEL SERVICIO DE INTERNET',
    ]);

    TipoEventoSignificativo::firstOrCreate(['id' => 2], [
        'codigo_clasificador' => 2,
        'descripcion' => 'INACCESIBILIDAD AL SERVICIO WEB DE LA ADMINISTRACION TRIBUTARIA',
    ]);
});

test('unauthorized users cannot visit the eventos significativos index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sincronizacion.eventos'))
        ->assertStatus(403);
});

test('authenticated users with permission can visit the eventos significativos index page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('sincronizacion.eventos'))
        ->assertStatus(200)
        ->assertSeeLivewire(EventosSignificativos::class);
});

test('can search and filter eventos significativos', function () {
    $user = User::factory()->admin()->create();

    EventoSignificativo::create([
        'punto_venta_id' => 1,
        'tipo_evento_significativo_id' => 1,
        'codigo_recepcion' => '9953999',
        'fecha_ini' => now()->subHours(2),
        'fecha_fin' => now()->subHour(),
        'descripcion' => 'Corte de fibra optica en sucursal',
        'estado' => 'REGISTRADO',
    ]);

    EventoSignificativo::create([
        'punto_venta_id' => 1,
        'tipo_evento_significativo_id' => 2,
        'codigo_recepcion' => '9954000',
        'fecha_ini' => now()->subHours(4),
        'fecha_fin' => now()->subHours(3),
        'descripcion' => 'Falla de suministro electrico por tormenta',
        'estado' => 'REGISTRADO',
    ]);

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->set('search', 'fibra optica')
        ->assertSee('9953999')
        ->assertDontSee('9954000');
});

test('can change perPage and reset pagination', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->assertSet('perPage', 10)
        ->set('perPage', 25)
        ->assertSet('perPage', 25);
});

test('can open and close create modal', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->assertSet('showCreateModal', false)
        ->call('abrirModalCrear')
        ->assertSet('showCreateModal', true)
        ->call('cerrarModalCrear')
        ->assertSet('showCreateModal', false);
});

test('validates required fields before submitting event', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->call('abrirModalCrear')
        ->set('descripcion', '')
        ->set('fecha_ini', '')
        ->set('fecha_fin', '')
        ->call('guardarEvento')
        ->assertHasErrors(['descripcion', 'fecha_ini', 'fecha_fin']);
});

test('can open and close detail modal', function () {
    $user = User::factory()->admin()->create();

    $evento = EventoSignificativo::create([
        'punto_venta_id' => 1,
        'tipo_evento_significativo_id' => 1,
        'codigo_recepcion' => '9954001',
        'fecha_ini' => now()->subHours(2),
        'fecha_fin' => now()->subHour(),
        'descripcion' => 'Test modal detalle',
        'estado' => 'REGISTRADO',
    ]);

    Livewire::actingAs($user)
        ->test(EventosSignificativos::class)
        ->call('verDetalle', $evento->id)
        ->assertSet('showDetalleModal', true)
        ->assertSet('detalleEventoId', $evento->id)
        ->call('cerrarModalDetalle')
        ->assertSet('showDetalleModal', false)
        ->assertSet('detalleEventoId', null);
});
