<?php

namespace App\Console\Commands;

use App\Services\SiatSincronizacionService;
use Illuminate\Console\Command;

class SincronizarCatalogosSiat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:sincronizar-catalogos {catalogo? : Catálogo específico a sincronizar (opcional)} {--sucursal= : ID de la sucursal} {--pv= : Código de punto de venta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los catálogos oficiales desde el Servicio de Impuestos Nacionales (SIAT Bolivia)';

    /**
     * Execute the console command.
     */
    public function handle(SiatSincronizacionService $servicio): int
    {
        $catalogo = $this->argument('catalogo');
        $sucursalId = $this->option('sucursal') ? (int) $this->option('sucursal') : null;
        $pvCodigo = $this->option('pv') !== null ? (int) $this->option('pv') : null;

        $this->info('Iniciando sincronización con el SIAT...');

        if ($catalogo) {
            $this->line("Sincronizando catálogo específico: {$catalogo}");
            $res = $servicio->sincronizarIndividual($catalogo, $sucursalId, $pvCodigo);
            if ($res['success']) {
                $this->info("✔ {$res['message']}");

                return Command::SUCCESS;
            } else {
                $this->error("✖ {$res['message']}");

                return Command::FAILURE;
            }
        }

        $this->line('Sincronizando todos los catálogos oficiales...');
        $res = $servicio->sincronizarTodos($sucursalId, $pvCodigo);

        $headers = ['Catálogo', 'Estado', 'Registros', 'Detalle'];
        $rows = [];

        foreach ($res['resultados'] as $cat => $r) {
            $rows[] = [
                $cat,
                $r['success'] ? '✔ EXITOSO' : '✖ FALLIDO',
                $r['count'],
                $r['message'],
            ];
        }

        $this->table($headers, $rows);

        $this->info("Sincronización finalizada. Total registros actualizados: {$res['total_actualizados']}");

        return $res['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}
