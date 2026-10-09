<?php

namespace App\Livewire\Sincronizacion;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class EventosSignificativos extends Component
{
    public function render(): View
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.eventos.ver'), 403, 'No tienes permiso para ver contingencias y eventos significativos.');

        return view('livewire.sincronizacion.eventos-significativos');
    }
}
